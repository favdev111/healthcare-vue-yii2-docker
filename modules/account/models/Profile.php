<?php

namespace modules\account\models;

use common\components\behaviors\HasGradeRelationBehavior;
use common\components\Formatter;
use common\components\validators\NameStringValidator;
use common\helpers\Location;
use common\models\City;
use common\models\GooglePlace;
use common\components\validators\GooglePlaceValidator;
use modules\account\components\ZipCodeValidator;
use modules\account\helpers\ConstantsHelper;
use modules\account\helpers\EventHelper;
use common\components\HtmlPurifier;
use modules\account\helpers\Timezone;
use Yii;
use yii\helpers\StringHelper;
use modules\account\models\ar\ListPatient;

/**
 * This is the model class for table "{{%account_profile}}".
 *
 * @property integer $id
 * @property integer $accountId
 * @property string $firstName
 * @property string $lastName
 * @property string $gender
 * @property integer $dateOfBirth
 * @property string $phoneNumber
 * @property string $zipCode
 * @property string $address
 * @property string $googlePlaceId
 * @property string $studentAvatarId
 * @property integer $latitude
 * @property integer $longitude
 * @property string $title
 * @property boolean $showFullName
 * @property string $description
 * @property integer $tutoringStudentHome
 * @property integer $tutoringMyHome
 * @property integer $tutoringPublicPlace
 * @property string $companyName
 * @property string $taxId
 * @property string $schoolName
 * @property integer $schoolGradeLevelId
 * @property string $schoolGradeLevel
 * @property string $isCertified
 * @property string $fullName
 *
 * @property Account $account
 * @property City $city
 * @property string $showName
 * @property string $startDate
 * @property Grade $grade
 * @property ListPatient $listPatient
 *
 * @method createGradeItem()
 *
 * Health pro fields
 *
 * @property  integer $professionalTypeId
 * @property  integer $doctorTypeId
 * @property  string $npiNumber
 * @property  integer $yearsOfExperience
 * @property  integer $isBoardCertified
 * @property  integer $hasDisciplinaryAction
 * @property  string $disciplinaryActionText
 * @property-read bool|array $zipCodeLocation
 * @property-read mixed $age
 * @property-write int $user
 * @property-read \yii\db\ActiveQuery|\common\models\GooglePlace $googlePlace
 * @property-read string $cityName
 * @property-read null $mainPhoneNumberType
 * @property-read string|mixed $genderName
 * @property-read mixed $formattedPhone
 * @property-read string|mixed $schoolGradeLevelName
 * @property-read \yii\db\ActiveQuery $zipCodeItem
 * @property  integer $currentlyEnrolled
 */
class Profile extends \common\components\ActiveRecord
{
    public $placeId;

    const EVENT_CHANGE_AVATAR = 'changeAvatar';
    const EVENT_CHANGE_EMAIL = 'changeEmail';

    const SCENARIO_EDIT_STUDENT = 'edit-student';
    const SCENARIO_EDIT_COMPANY_CLIENT = 'edit-company-client';

    const UPDATE_COMPANY_PROFILE_SCENARIO = 'updateCompanyProfile';
    const SCENARIO_ADMIN_EDIT_EMPLOYEE = 'adminEditEmployee';
    const SCENARIO_SING_UP_SPECIALIST = 'specialist_sign_up_1_step';

    const MAX_TITLE_LENGTH = 70;

    public static $editRequiredAttributes = ['title', 'description'];

    protected $geocodeService;

    public function init()
    {
        $this->module = Yii::$app->getModuleAccount();
        $this->geocodeService = Yii::$app->geocoding;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account_profile}}';
    }

    public function behaviors()
    {
        return [
            'grade' => [
                'class' => HasGradeRelationBehavior::class,
                'type' => GradeItem::TYPE_ACCOUNT_PROFILE,
            ],
        ];
    }

    /**
     * Common profile rules
     */
    public static function rulesCommon()
    {
        /**
         * @var Formatter $formatter
         */
        $formatter = Yii::$app->formatter;

        return [
            [['firstName', 'lastName', 'phoneNumber', 'address', 'googlePlaceId', 'placeId'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value, ['HTML.Allowed' => '']);
            }
            ],
            [['firstName', 'lastName'], NameStringValidator::class],
            ['firstName', 'filter', 'filter' => function ($value) {
                return ucwords(strtolower($value));
            }
            ],
            ['lastName', 'filter', 'filter' => function ($value) {
                return ucwords(strtolower($value));
            }
            ],
            [
                'placeId',
                GooglePlaceValidator::class,
            ],
            [['firstName', 'lastName'], 'string', 'max' => 255],
            ['phoneNumber', 'string', 'max' => 10],
            ['isCertified', 'boolean'],
            [['phoneNumber'], 'required', 'on' => self::SCENARIO_SING_UP_SPECIALIST],
            ['phoneNumber', 'udokmeci\yii2PhoneValidator\PhoneValidator', 'country' => 'US', 'format' => false],
            ['gender', 'in', 'range' => array_keys(static::getGenderArray())],
            ['dateOfBirth', 'required'],
            [
                'dateOfBirth',
                'date',
                'format' => 'php:m/d/Y',
                'timestampAttribute' => 'dateOfBirth',
                'timestampAttributeFormat' => 'php:Y-m-d',
                'max' => date('m/d/Y', strtotime('-18 year')),
                'tooBig' => 'To Submit application you should be 18+ years old'
            ],
            [
                ['startDate'],
                'date',
                'format' => 'php:' . $formatter->MYSQL_DATE,
                'min' => date($formatter->MYSQL_DATE),
                'minString' => date($formatter->dateWithSlashesPhp)
            ],
            [['startDate'], 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) use ($formatter) {
                return Timezone::staticConvertToServerTimeZone(
                    $value,
                    $formatter->MYSQL_DATE . ' ' . $formatter->MIDDAY_HOUR,
                    12
                );
            }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            static::rulesCommon(),
            [
                [['title', 'description', 'zipCode'], function ($attribute) {
                    $this->$attribute = HtmlPurifier::process($this->$attribute, ['HTML.Allowed' => '']);
                }
                ],
                'zipCodeRequired' => [['zipCode'], 'required'],
                [['zipCode'], ZipCodeValidator::class],
                [['firstName', 'lastName'], 'required'],
                [['phoneNumber'], 'required', 'when' => function ($model) {
                    return ! empty($model->account);
                }
                ],
                [['description'], 'string'],
                [['title'], 'trim'],
                [['title'], 'string', 'max' => self::MAX_TITLE_LENGTH],
                [['description', 'title'], 'required', 'on' => 'dashboardProfile'],
                [['phoneNumber'], 'required', 'on' => 'edit'],
                ['showFullName', 'boolean'],
                ['showFullName', 'default', 'value' => false],
            ]
        );
    }

    public function getDescription()
    {
        $pattern = '~[a-z]+://\S+~';

        if (preg_match_all($pattern, $this->description, $links)) {
            $replaced = [];
            foreach ($links[0] as $link) {
                if (in_array($link, $replaced)) {
                    continue;
                }
                $this->description = str_replace(
                    $link,
                    '<a href="' . $link . '">' . $link . '</a>',
                    $this->description
                );
                $replaced[] = $link;
            }
        }
        $this->description = \common\components\StringHelper::hideEmail($this->description);
        $this->description = \common\components\StringHelper::hidePhoneNumber($this->description);

        return $this->description;
    }


    public function getTruncateDescription($seeMoreLink, $shortDescription = true, $nl2br = true)
    {
        $description = trim($this->getDescription());

        $descriptionArray = explode("\n", $description);
        $descriptionArray = array_splice($descriptionArray, 0, 3);
        $maxCountBr = 3;
        $maxCountLetters = $shortDescription ? 150 : 240;
        $lettersPerRow = $maxCountLetters / $maxCountBr;
        $outputDescription = '';
        foreach ($descriptionArray as $key => $description) {
            $strLength = mb_strlen($description);
            $countRows = floor($strLength / $lettersPerRow);
            if ($countRows >= $maxCountBr) {
                $outputDescription .= \yii\helpers\StringHelper::truncate(
                    $description,
                    $maxCountLetters,
                    '... ' . $seeMoreLink,
                    null,
                    true
                );
                break;
            } else {
                $outputDescription .= $description . "\n";
            }
            $maxCountBr -= ++$countRows;
            $maxCountLetters -= $lettersPerRow * $countRows;
        }

        $outputDescription = trim($outputDescription);

        return $nl2br ? nl2br($outputDescription) : $outputDescription;
    }

    /**
     * @param $length
     * @return string
     */
    public function getTruncateDescriptionMobile($length)
    {
        return StringHelper::truncate($this->getDescription(), $length, '...', null, true);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenario = parent::scenarios();
        $scenario['edit'] = ['firstName', 'lastName', 'placeId', 'phoneNumber', 'showFullName', 'isCertified'];
        $scenario['edit-backend'] = ['firstName', 'lastName', 'placeId', 'phoneNumber', 'showFullName', 'dateOfBirth', 'isCertified'];
        $scenario['dashboardProfile'] = ['title', 'description'];
        $scenario[static::SCENARIO_EDIT_STUDENT] = ['firstName', 'lastName', 'phoneNumber', 'zipCode'];
        $scenario[static::SCENARIO_EDIT_COMPANY_CLIENT] = ['firstName', 'lastName', 'phoneNumber', 'placeId'];
        $scenario[static::UPDATE_COMPANY_PROFILE_SCENARIO] = ['companyName', 'placeId', 'taxId', 'phoneNumber', 'firstName', 'lastName', 'dateOfBirth'];
        $scenario[static::SCENARIO_ADMIN_EDIT_EMPLOYEE] = ['firstName', 'lastName', 'phoneNumber'];

        return $scenario;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accountId' => 'Account ID',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'gender' => 'Gender',
            'dateOfBirth' => 'Date of birth',
            'phoneNumber' => 'Phone Number',
            'zipCode' => 'Zip Code',
            'address' => 'Address',
            'avatarUrl' => 'Avatar Url',
            'title' => 'Title',
            'description' => 'Description',
            'tutoringStudentHome' => 'Tutoring Student Home',
            'tutoringMyHome' => 'Tutoring My Home',
            'tutoringPublicPlace' => 'Tutoring Public Place',
            'showFullName' => 'Show Full Name',
            'companyName' => 'Company Name',
            'taxId' => 'Tax ID',
        ];
    }

    public function getGenderName()
    {
        $genders = $this->getGenderArray();

        return $genders[$this->gender] ?? '';
    }

    public function getMainPhoneNumberType()
    {
        /**
         * @var PhoneValidation $phoneValidation
         */
        $phoneValidation = $this->account->accountMainPhoneNumber->phoneValidation ?? null;

        return $phoneValidation->type ?? null;
    }

    /**
     * @return array
     */
    public static function getGenderArray()
    {
        return ConstantsHelper::gender();
    }

    /**
     * @return array
     */
    public static function getSchoolGradeLevelArray()
    {
        return ConstantsHelper::schoolGradeLevel();
    }

    public function getSchoolGradeLevelName()
    {
        $array = self::getSchoolGradeLevelArray();

        return $array[$this->schoolGradeLevelId] ?? '';
    }

    /**
     * @return bool
     */
    public function isGenderMale()
    {
        return $this->gender === ConstantsHelper::GENDER_MALE;
    }

    /**
     * @return bool
     */
    public function isGenderFemale()
    {
        return $this->gender === ConstantsHelper::GENDER_FEMALE;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        $account = $this->module->modelStatic('AccountWithoutRestrictions');

        return $this->hasOne($account, ['id' => 'accountId']);
    }

    /**
     * @return \yii\db\ActiveQuery|GooglePlace
     */
    public function getGooglePlace()
    {
        return $this->hasOne(GooglePlace::class, ['id' => 'googlePlaceId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        $model = Location::getModelCity();

        return $this->hasOne(
            $model::className(),
            ['id' => 'cityId']
        )->viaTable(
            '{{%location_zipcode}}',
            ['code' => 'zipCode']
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZipCodeItem()
    {
        $model = Location::getModelZipCode();

        return $this->hasOne($model::className(), ['code' => 'zipCode']);
    }

    /**
     * @return array|boolean
     */
    public function getZipCodeLocation()
    {
        return Location::getZipcodeLocation($this->zipCode);
    }

    public function getCityName()
    {
        $city = $this->city;

        return $city->name ?? '';
    }

    public function locationDistance($latitude, $longitude)
    {
        if (
            empty($this->latitude)
            || empty($this->longitude)
        ) {
            return false;
        }

        return Location::getDistance(
            $latitude,
            $longitude,
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * setting it as internal to prevent its usage everywhere
     * @return string
     * @internal
     */
    public function fullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Set user id
     * @param int $accountId
     * @return static
     */
    public function setUser($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * @return bool
     */
    private function isRequiredAttributesChanged()
    {
        foreach (self::$editRequiredAttributes as $attribute) {
            if (in_array($attribute, $this->getChangedAttributes())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->isRequiredAttributesChanged()) {
            EventHelper::editProfileRequiredFields($this->account);
        }

        EventHelper::changeProfileEvent(
            $this,
            $insert,
            $changedAttributes
        );

        parent::afterSave($insert, $changedAttributes);
    }

    public function getShowName(
        $limit = null,
        $addManagedByForCompanyClients = true,
        $checkCurrentIdentity = true
    ) {
        $name = $this->shortName();
        if ($this->showFullName) {
            if (empty($limit) || (is_int($limit) && (mb_strlen($this->fullName()) <= $limit))) {
                $name = $this->fullName();
            }
        }

        return $name;
    }

    public function shortName()
    {
        return $this->firstName . ' ' . StringHelper::truncate($this->lastName, 1, '') . '.';
    }

    public function getAge()
    {
        $from = new \DateTime($this->dateOfBirth);
        $to = new \DateTime('today');

        return $from->diff($to)->y;
    }

    public function beforeSave($insert)
    {
        if (! empty($this->firstName)) {
            $this->firstName = ucwords(strtolower($this->firstName));
        }
        if (! empty($this->lastName)) {
            $this->lastName = ucwords(strtolower($this->lastName));
        }
        if (
            ! empty($this->placeId)
            && ($data = $this->geocodeService->getZipCodeByPlaceId($this->placeId))
        ) {
            $this->zipCode = $data->zipCode;
            $this->latitude = $data->latitude;
            $this->longitude = $data->longitude;
            $this->address = $data->address;
            $this->googlePlaceId = $data->googlePlaceId;
        }

        return parent::beforeSave($insert);
    }

    public function getFormattedPhone()
    {
        $phone = substr_replace('(' . $this->phoneNumber, ') ', 4, 0);

        return substr_replace($phone, '-', 9, 0);
    }
    /**
     * Gets query for [[ListPatient]].
     *
     * @return \yii\db\ActiveQuery|\common\models\query\ListPatientQuery
     */
    public function getAccountPatient()
    {
        return $this->hasOne(ListPatient::class, ['id' => 'accountId']);
    }
     /**
     * {@inheritdoc}
     * @return \common\models\query\ProfileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ProfileQuery(get_called_class());
    }
}
