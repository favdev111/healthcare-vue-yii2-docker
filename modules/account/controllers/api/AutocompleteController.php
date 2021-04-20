<?php

namespace modules\account\controllers\api;

use common\components\HtmlPurifier;
use common\models\Zipcode;
use modules\account\models\SubjectSearch;
use Yii;
use modules\account\models\EducationCollege;
use yii\web\NotFoundHttpException;

/**
 * Default controller for User module
 */
class AutocompleteController extends \api\components\Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    public function actionCollege()
    {
        $query = EducationCollege::find();

        $searchName = Yii::$app->request->get('q', Yii::$app->request->get('query'));

        $query->andFilterWhere(['LIKE', 'name', '%' . $searchName . '%', false])->limit(30);

        $result = [];
        foreach ($query->all() as $data) {
            $result[] = [
                'text' => $data->fullName,
                'id' => $data->id,
            ];
        }

        return ['results' => $result];
    }

    public function actionCollegeById($id)
    {
        $college = EducationCollege::find()->andWhere(['id' => $id])->one();

        return $college ? $college->fullName : null;
    }

    /**
     * @deprecated
     */
    public function actionSubjectsWithoutCat()
    {
        $searchKeyword = Yii::$app->request->get('q');
        $ss = new SubjectSearch();
        $result = $ss->searchWeight($searchKeyword, false);

        return ['results' => $result];
    }

    /**
     * @OA\Get(
     *     path="/auto/subjects/{query}/",
     *     tags={"autocomplite"},
     *     summary="List of subjects",
     *     description="",
     *     security={{"Bearer":{}}},
     *     @OA\Parameter(
     *         description="Search keyword",
     *         in="path",
     *         name="query",
     *         required=true,
     *         type="string"
     *     ),
     *     @OA\Parameter(
     *         description="Search in categories to",
     *         in="query",
     *         name="withCategory",
     *         required=false,
     *         type="integer"
     *     ),
     *     @OA\Parameter(
     *         description="Exclude category subjects",
     *         in="query",
     *         name="excludeCategorySubjects",
     *         required=false,
     *         type="boolean"
     *     ),
     *     @OA\Response(response="200", description="")
     * )
     */
    public function actionSubjects($query)
    {
        $withCategory = (bool) Yii::$app->request->getQueryParam('withCategory', false);
        $excludeCategorySubjects = (bool) Yii::$app->request->get('excludeCategorySubjects', true);
        return $this->subjects(
            $query,
            $withCategory,
            $excludeCategorySubjects
        );
    }

    /**
     * @param string $query
     * @param bool   $withCategory
     * @param bool   $excludeCategorySubjects
     *
     * @return array
     */
    protected function subjects($query, $withCategory = true, $excludeCategorySubjects = true)
    {
        $query = trim($query);
        $query = HtmlPurifier::process($query);
        return (new SubjectSearch())->searchWeight($query, $withCategory, $excludeCategorySubjects);
    }

    /**
     * @deprecated
     */
    public function actionSubjectsOld()
    {
        $searchKeyword = Yii::$app->request->get('q');
        $excludeCategorySubjects = Yii::$app->request->get('excludeCategorySubjects', true);
        $ss = new SubjectSearch();
        $result = $ss->searchWeight($searchKeyword, true, $excludeCategorySubjects);

        return ['results' => $result];
    }

    /**
     * need for selectize
     * @return array
     */
    public function actionSubjectsSelectize()
    {
        $searchKeyword = Yii::$app->request->get('query');
        $ss = new SubjectSearch();
        $result = $ss->searchWeight($searchKeyword);

        return $result;
    }

    public function actionCityByZipcode($zipcode)
    {
        $zipCode = Zipcode::find()->joinWith('city')->select('name')->andWhere(['code' => $zipcode])->column();
        if (!empty($zipCode)) {
            return $zipCode[0];
        }
        throw new NotFoundHttpException();
    }
}
