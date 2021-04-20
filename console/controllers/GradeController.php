<?php

namespace console\controllers;

use common\models\ClientChild;
use modules\account\models\GradeItem;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\Console;

class GradeController extends Controller
{
    protected function update($gradeItem)
    {
        $gradeItem->toNextGrade();
        $gradeItem->save(false);
    }

    public function actionUpdate($id = null)
    {
        if ($id) {
            $gradeItem = GradeItem::findOne($id);
            $this->update($gradeItem);
        } else {
            $query = GradeItem::find()->active();
            /**
             * @var GradeItem $gradeItem
             */
            foreach ($query->each() as $gradeItem) {
                $this->update($gradeItem);
            }
        }
    }

    protected function fill($child)
    {
        $gi = $child->gradeItem;
        if (empty($gi)) {
            $gi = $child->createGradeItem();
        }
        $oldGradeLevel = $child->schoolGradeLevel;
        if (!empty($oldGradeLevel) && $oldGradeLevel >= 1 && $oldGradeLevel <= 12) {
            //in new grade list 1-st school grade level has id = 6;
            $gi->gradeId = $oldGradeLevel + 5;
            $gi->save(false);
        }
    }
    public function actionFill($id = null)
    {
        if ($id) {
            $child = ClientChild::findById($id);
            if (empty($child)) {
                throw new Exception('Child not found');
            }
            $this->fill($child);
        } else {
            $query = ClientChild::find();
            /**
             * @var ClientChild $child
             */
            $i = 0;
            $total = (clone $query)->count();
            Console::startProgress(0, $total, "Update children count: ");
            foreach ($query->each() as $child) {
                try {
                    $this->fill($child);
                } catch (\Throwable $exception) {
                    echo $exception->getMessage() . " " . $exception->getTraceAsString();
                } finally {
                    $i++;
                    Console::updateProgress($i, $total, "Update children count: ");
                }
            }
            Console::endProgress();
        }
    }
}
