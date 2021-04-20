<?php

namespace console\controllers;

use common\helpers\LandingPageHelper;
use common\helpers\Location;
use modules\account\models\ar\State;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class LocationController extends Controller
{
    protected function fillStates()
    {
        foreach ($states = State::STATES_ARRAY as $shortName => $name) {
            $query = State::find()->andWhere([
                'name' => $name,
                'shortName' => $shortName,
            ]);

            if ($query->exists()) {
                continue;
            } else {
                $arStatesModel = new \modules\account\models\ar\State();
                $arStatesModel->name = $name;
                $arStatesModel->shortName = $shortName;
                $arStatesModel->slug = LandingPageHelper::slug($name);
                $arStatesModel->save();
            }
        }
    }
    public function actionDownloadIpDb()
    {
        Yii::$app->geoIp->updateDb();
    }

    public function actionUpdate($update = false)
    {
        $this->fillStates();
        ini_set('auto_detect_line_endings', true);

        //Download file
        $url = 'http://download.geonames.org/export/zip/US.zip';
        $zipFile = tempnam(Yii::getAlias('@runtime'), 'Zip'); // Local Zip File Path
        $zipResource = fopen($zipFile, "w");

        // Get The Zip File From Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $zipResource);
        $page = curl_exec($ch);
        if (!$page) {
            Console::error("Error :- " . curl_error($ch));
            return ExitCode::UNSPECIFIED_ERROR;
        }
        curl_close($ch);

        /* Open the Zip file */
        $zip = new \ZipArchive();
        $extractPath = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'zipcode' . DIRECTORY_SEPARATOR;
        if ($zip->open($zipFile) != "true") {
            Console::error("Error :- Unable to open the Zip File");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        /* Extract Zip File */
        $zip->extractTo($extractPath);
        $zip->close();
        unlink($zipFile);

        $files = scandir($extractPath);
        foreach ($files as $file) {
            $filePath = $extractPath . $file;
            if (
                !is_file($filePath)
                || ($file === 'readme.txt')
            ) {
                continue;
            }

            Console::stdout('File: ' . $file . "\n");
            $count = intval(exec("wc -l '{$filePath}'"));
            Console::startProgress(0, $count);
            $handle = fopen($filePath, 'r');
            $i = 1;
            while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                Location::addLocation(
                    $data[2],
                    $data[3],
                    $data[4],
                    $data[1],
                    $data[9],
                    $data[10],
                    $update
                );

                Console::updateProgress($i++, $count);
            }

            Console::endProgress();

            unlink($filePath);
        }

        return ExitCode::OK;
    }
}
