<?php

namespace modules\payment\models\api;

use common\components\behaviors\ChildModelErrorsBehavior;
use modules\payment\models\api\PostPayment;
use modules\account\models\api\AccountClient;
use modules\account\models\Token;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;

/* Uncomment all TODO's with label POST-PAYMENT to enable post-payment functionality in PaymentCustomer*/
/**
 * @inheritdoc
 */
class PaymentCustomer extends \modules\payment\models\PaymentCustomer
{
    public $newActiveBankAccountId;
    /*public $postPayment; TODO uncomment this line to enable POST-PAYMENT functionality*/

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [
            'newActiveBankAccountId',
            'exist',
            'skipOnEmpty' => true,
            'skipOnError' => true,
            'targetClass' => PaymentBankAccount::className(),
            'targetAttribute' => 'id',
            'filter' => function ($query) {
                /**
                 * @var $query ActiveQuery
                 */
                $query->andWhere(['paymentCustomerId' => $this->id]);
            },
        ];
        /*$rules[] = [['postPayment'], 'safe']; TODO uncomment this line to enable POST-PAYMENT functionality*/
        $rules['autorenew_default'] = ['autorenew', 'default', 'value' => true];
        return $rules;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'childModelErrors' => ChildModelErrorsBehavior::className(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($id)
    {
        $query = parent::findByCondition(['id' => $id]);
        return $query->one();
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        $query = parent::find();
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findByCondition($condition)
    {
        $query = parent::findByCondition($condition);
        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findBySql($sql, $params = [])
    {
        $query = parent::findBySql($sql, $params);
        return $query;
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['customerId']);
        $fields['packagePrice'] = function () {
            return $this->packagePrice === null ? $this->packagePrice : intval($this->packagePrice);
        };
        $fields['paymentUrlToken'] = function () {
            $token = Token::findByUser($this->accountId, Token::TYPE_CLIENT_PAYMENT);
            if (!$token) {
                $token = Token::generate($this->accountId, Token::TYPE_CLIENT_PAYMENT);
            }
            return $token->token;
        };
        /*$fields['postPayments'] = 'postPayments'; TODO uncomment this line to enable POST-PAYMENT functionality*/

        // TODO: Remove this part once mentioned fields are removed from DB
        $billingAddressFields = [
            'address',
            'zipcode',
            'apartment',
        ];
        foreach ($billingAddressFields as $billingAddressField) {
            if (isset($fields[$billingAddressField])) {
                unset($fields[$billingAddressField]);
            }
        }

        return $fields;
    }

    public function getPostPayments()
    {
        return $this->hasMany(PostPayment::className(), ['accountId' => 'id'])->via('account');
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();
        $extraFields['cards'] = 'cardInfo';
        return $extraFields;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->newActiveBankAccountId) {
            /**
             * @var $model \modules\payment\models\PaymentBankAccount
             */
            $model = $this->getBankAccounts()->andWhere(['id' => $this->newActiveBankAccountId])->one();
            if ($model) {
                $model->active = true;
                if (!$model->save(['active'])) {
                    $this->addError('newActiveBankAccountId', $model->getFirstError('active'));
                    return false;
                }
            }
        }
        return true;
    }

    public function fillPostPayment()
    {
        if (!empty($this->postPayment)) {
            $newPostPayment = new PostPayment();
            $newPostPayment->accountId = $this->accountId;
            $newPostPayment->amount = $this->postPayment['amount'] ?? null;
            $newPostPayment->date = $this->postPayment['date'] ?? null;
            $newPostPayment->save(true);
            if ($newPostPayment->hasErrors()) {
                $this->collectErrors($newPostPayment);
            }
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        /*$this->fillPostPayment(); TODO uncomment this line to enable POST-PAYMENT functionality*/
        parent::afterSave($insert, $changedAttributes);
    }
}
