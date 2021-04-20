<?php

namespace console\controllers;

use modules\account\models\FileModel;
use yii\console\Controller;
use Yii;

/**
 * Class FilesController
 * @package console\controllers
 */
class FilesController extends Controller
{
    /**
     *
     */
    public function actionCheck()
    {
        try {
            $files = FileModel::find()
                ->andWhere(['status' => FileModel::STATUS_DETACHED])
                ->all();
            foreach ($files as $file) {
                $fileDateCreated = new \DateTime($file->createdAt);
                $fileDateCreated->modify('+1 day');

                if ($fileDateCreated->format('Y-m-d H:i:s') < (new \DateTime())->format('Y-m-d H:i:s')) {
                    if (!Yii::$app->fileSystem->has($file->file_name)) {
                        echo 'File: ' . $file->file_name . " does not exist at storage\n";
                        $file->delete();
                        continue;
                    }

                    if (Yii::$app->fileSystem->delete($file->file_name)) {
                        $file->delete();
                    } else {
                        echo "File can't delete from storage \n";
                    }
                }
            }
        } catch (\Exception $exception) {
            echo "Catch exception: " . $exception->getMessage() . "\n";
        }
    }
}
