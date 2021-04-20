<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%insurance_company}}`.
 */
class m200922_155521_create_insurance_company_table extends Migration
{
    public $companies = [
        ['UnitedHealthcare'],
        ['Kaiser Permanente'],
        ['Anthem (Blue Cross Blue Shield)'],
        ['Aetna'],
        ['Blue Cross Blue Shield'],
        ['WellCare'],
        ['Humana'],
        ['CVS'],
        ['Health Care Service Corporation (HCSC)'],
        ['Centene Corp'],
        ['Cigna Health'],
        ['Wellcare'],
        ['Molina Healthcare Inc.'],
        ['Guidewell Mut Holding'],
        ['California Physicians Service'],
        ['Independence Health Group Inc.'],
        ['Highmark Group'],
        ['Caresource'],
        ['Carefirst, Inc.'],
        ['Health Net of California, Inc.'],
        ['UPMC Health System'],
        ['Metropolitan'],
    ];

    private $table = '{{%insurance_company}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
        ]);

        Yii::$app->db->queryBuilder->batchInsert($this->table, ['name'], $this->companies);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
