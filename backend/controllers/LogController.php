<?php

namespace backend\controllers;

use backend\components\controllers\Controller;
use yii\web\HttpException;

class LogController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    protected function clearDirList(array &$dirList)
    {
        if (($key = array_search('.', $dirList)) !== false) {
            unset($dirList[$key]);
        }
        if (($key = array_search('..', $dirList)) !== false) {
            unset($dirList[$key]);
        }
    }

    protected function getLogList(): array
    {
        $logPath = \Yii::getAlias('@log');
        $dirList = scandir($logPath);
        $this->clearDirList($dirList);

        $pathList = [];

        foreach ($dirList as $dir) {
            $scanResult = scandir($logPath . '/' . $dir);
            $this->clearDirList($scanResult);
            foreach ($scanResult as $file) {
                $name = $dir . DIRECTORY_SEPARATOR . $file;
                $pathList[$name] = $name;
            }
        }
        return $pathList;
    }

    protected function prepareFileContent(string $selectedFile): string
    {
        $fileContent = file_get_contents(\Yii::getAlias('@log' . DIRECTORY_SEPARATOR .  $selectedFile));
        $fileContent = explode("\n", $fileContent);
        $reg = '/^(\d{4}(-\d{2}){2} (\d{2}:){2}\d{2}) (\[[^\]]+\]){3}\[([^\]]+)\]\[([^\]]+)\]/i';
        $matches =  [];
        $groups = [];
        $i = 0;
        $groups[0] = [];
        foreach ($fileContent as &$line) {
            if (preg_match($reg, $line, $matches)) {
                //start new group if match found
                $i++;
                $groups[$i] = [];
                //implode previous group
                $groups[$i - 1] = implode('<br>', $groups[$i - 1]);
                $level = trim($matches[5]);
                switch ($level) {
                    case 'error':
                        $color = 'red';
                        break;
                    case 'info':
                        $color = 'blue';
                        break;
                    case 'warning':
                        $color = 'orange';
                        break;
                    default:
                        $color = null;
                }
                if ($color) {
                    $line = '<span class="' . $color .  ' ">' . $line . '</span>';
                }
            }
            $groups[$i][] = $line;
        }
        //reverse groups (last should be displayed first)
        if (is_array($groups[$i])) {
            $groups[$i] = implode('<br>', $groups[$i]);
        }
        $groups = array_reverse($groups);

        return addcslashes(nl2br(implode('<br><br>', $groups)), '');
    }

    public function actionIndex()
    {
        \Yii::$app->view->title = "Logs";
        $logList = $this->getLogList();
        $selectedFile = (string)\Yii::$app->request->get('logFile') ?: reset($logList);
        if (!empty($selectedFile)) {
            $fileContent = $this->prepareFileContent($selectedFile);
        }
        return $this->render(
            'index',
            [
                'pathList' => $logList,
                'selectedFile' => $selectedFile,
                'fileContent' => $fileContent ?? null
            ]
        );
    }

    public function actionDownload()
    {
        $fileName = (string)\Yii::$app->request->get('logFile');
        if (empty($fileName)) {
            throw new HttpException(400);
        }
        $filePath = \Yii::getAlias('@log/' . $fileName);
        \Yii::$app->response->sendFile(
            $filePath,
            str_replace('/', '.', $fileName)
        );
    }
}
