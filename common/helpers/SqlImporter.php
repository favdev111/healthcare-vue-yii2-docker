<?php

namespace common\helpers;

use yii\db\Exception;

/**
 * Class SqlImporter
 * @package app\helpers
 */
class SqlImporter
{
    /**
     * @var string alias to the SQL file
     */
    public $filename;

    /**
     * Imports the given sql file to the
     * @throws \Exception
     */
    public function import()
    {
        $filename = \Yii::getAlias($this->filename);
        if (!file_exists($filename)) {
            throw new \Exception('Error while importing ' . $filename . '. File not found.');
        }
        // Read in entire file
        $fp = fopen($filename, 'r');
        // Temporary variable, used to store current query
        $templine = '';
        // Loop through each line
        while (($line = fgets($fp)) !== false) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }
            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                try {
                    \Yii::$app->getDb()->createCommand($templine)->execute();
                } catch (Exception $e) {
                    throw new \Exception('Error while importing ' . $filename . '. Error details: ' . $e->getMessage());
                }
                // Reset temp variable to empty
                $templine = '';
            }
        }
        //close the file
        fclose($fp);
    }
}
