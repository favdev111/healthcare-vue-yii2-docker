<?php

namespace modules\account\components;

use Yii;
use common\components\ImmutableObject;
use yii\base\InvalidParamException;

class TutorDataObject extends ImmutableObject
{
    public $firstSubjectName = '';
    public $subjectsCount = 0;
    public $locationDistance = null;

    protected $_accountId;

    private $model;

    protected function init(array $config = [])
    {
        if (!isset($config['accountId'])) {
            throw new InvalidParamException('"accountId" required parameter.');
        }

        $this->_accountId = (int)$config['accountId'];

        $this->fillData($this->getModel());
    }

    /**
     * @return \modules\account\models\Account
     */
    public function getModel()
    {
        if ($this->model) {
            return $this->model;
        }

        $modelClass = Yii::$app->getModule('account')->modelStatic('Account');
        $model = $modelClass::findOne($this->_accountId);

        if (!$model) {
            throw new InvalidParamException('Account (ID=' . $this->_accountId . ') not found.');
        }

        $this->model = $model;

        return $this->model;
    }

    public function __sleep()
    {
        return ['_attributes', '_accountId'];
    }

    protected function fillData($model)
    {
        $accountAttributes = [
            'id',
            'countReview',
            'totalArticulation',
            'totalProficiency',
            'totalPunctual',
            'avgResponseTime',
            'avatarUrl',
            'totalTeachHours',
            'profileUrl',
        ];
        foreach ($accountAttributes as $accountAttribute) {
            $this->_attributes->{$accountAttribute} = $model->{$accountAttribute};
        }

        $profileAttributes = [
            'showName',
            'cityName',
            'title',
        ];
        $profileModel = $model->profile;
        foreach ($profileAttributes as $profileAttribute) {
            $this->_attributes->{$profileAttribute} = $profileModel->{$profileAttribute};
        }

        $this->_attributes->fullRate = $model->rate->fullRate;
        $this->_attributes->totalRating = $model->rating->totalRating ?? 0;
        $this->_attributes->collegeName = $model->educations[0]->college->fullName ?? null;
        $this->_attributes->descriptionTruncated = $profileModel->getTruncateDescription(null, !empty($model->rating->totalRating), false);
        $this->_attributes->avgResponseTimeFormatted = $model->getAvgResponseTimeFormat($model->avgResponseTime);
    }

    public function fillSubjectData($subject, $category)
    {
        $query = $this->getModel()->getAccountSubjects();
        if (!empty($category)) {
            $newQuery = clone($query);
            $newQuery->andWhere(['id' => $category->getSubjects()->select('id')->column()]);
            $query = $newQuery;
        } elseif (!empty($subject)) {
            $newQuery = clone($query);
            $newQuery->andWhere(['id' => $subject->id]);
            $checkQuery = clone($newQuery);
            if ($checkQuery->exists()) {
                $query = $newQuery;
            }
        }

        $this->firstSubjectName = $query->select('name')->limit(1)->scalar();
        $this->subjectsCount = $this->getModel()->countSubjects;

        return $this;
    }

    public function fillLocationDistance($elasticSearchModel, $modelSearch)
    {
        if (isset($elasticSearchModel) && !empty($elasticSearchModel->distanceCalc)) {
            $this->locationDistance = round($elasticSearchModel->distanceCalc, 2);
        } elseif (isset($modelSearch) && $modelSearch->zipCodeLocation) {
            $this->locationDistance = $this->getModel()->profile->locationDistance(
                $modelSearch->zipCodeLocation['latitude'],
                $modelSearch->zipCodeLocation['longitude']
            );
        }

        return $this;
    }
}
