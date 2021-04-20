<?php

use api2\helpers\parser\adapter\CDCGovAdapter as CDCGovAdapter;
use api2\helpers\parser\adapter\MayoClinicAdapter as MayoClinicAdapter;
use api2\helpers\parser\adapter\NHSInformAdapter;
use api2\helpers\parser\store\ConditionDbStoreStrategy;
use yii\db\Migration;

/**
 * Class m201014_090509_import
 */
class m201014_090509_import extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $parser = new \api2\helpers\parser\Parser();

        $parser->parseAndStore(
            new CDCGovAdapter(),
            new ConditionDbStoreStrategy()
        );

        $parser->parseAndStore(
            new NHSInformAdapter(),
            new ConditionDbStoreStrategy()
        );

        $parser->parseAndStore(
            new MayoClinicAdapter(),
            new ConditionDbStoreStrategy()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \common\models\health\MedicalCondition::deleteAll('1');
    }
}
