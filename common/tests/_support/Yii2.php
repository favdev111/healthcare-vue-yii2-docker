<?php

namespace common\tests\_support;

use Codeception\Lib\Connector\Yii2 as Yii2Connector;
use Codeception\TestInterface;
use yii\db\ActiveRecord;

/**
 * Class Yii2
 *
 * @package api\tests\_support
 */
class Yii2 extends \Codeception\Module\Yii2
{
    /**
     * Helper to manage database connections
     * @var Yii2Connector\ConnectionWatcher
     */
    private $connectionWatcher;

    /**
     * @inheritDoc
     */
    public function _beforeSuite($settings = [])
    {
        $this->recreateClient();
        $this->client->startApp();

        $this->connectionWatcher = new Yii2Connector\ConnectionWatcher();
        $this->connectionWatcher->start();

        $this->startTransactions();

        parent::_beforeSuite($settings);
    }

    /**
     * @inheritDoc
     */
    public function _afterSuite()
    {
        $_SESSION = [];
        $_FILES = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];

        $this->rollbackTransactions();

        if ($this->client !== null) {
            $this->client->resetApplication();
        }

        if (isset($this->connectionWatcher)) {
            $this->connectionWatcher->stop();
            $this->connectionWatcher->closeAll();
            unset($this->connectionWatcher);
        }

        parent::_afterSuite();
    }

    /**
     * @inheritDoc
     */
    public function _before(TestInterface $test)
    {
        if ($test instanceof \Codeception\Test\Cest) {
            $this->loadFixtures($test->getTestClass());
        } else {
            $this->loadFixtures($test);
        }
    }

    /**
     * @inheritDoc
     */
    public function _after(TestInterface $test)
    {
        \yii\web\UploadedFile::reset();

        if ($this->config['cleanup']) {
            foreach ($this->loadedFixtures as $fixture) {
                $fixture->unloadFixtures();
            }
            $this->loadedFixtures = [];
        }
    }

    /**
     * @inheritDoc
     */
    private function loadFixtures($test)
    {
        $this->debugSection('Fixtures', 'Loading fixtures');
        if (
            empty($this->loadedFixtures)
            && method_exists($test, $this->_getConfig('fixturesMethod'))
        ) {
            $connectionWatcher = new Yii2Connector\ConnectionWatcher();
            $connectionWatcher->start();
            $this->haveFixtures(call_user_func([$test, $this->_getConfig('fixturesMethod')]));
            $connectionWatcher->stop();
            $connectionWatcher->closeAll();
        }
        $this->debugSection('Fixtures', 'Done');
    }

    /**
     * Grab random record
     *
     * @param string $model ActiveRecord model class
     *
     * @return ActiveRecord
     */
    public function grabRandomRecord($model)
    {
        if (!class_exists($model)) {
            throw new \RuntimeException("Class $model does not exist");
        }

        $sql = 'SELECT * FROM ' . $model::tableName()
            . ' WHERE id >= RAND() * (SELECT MAX(id) FROM ' . $model::tableName() . ') LIMIT 1';

        return $model::findBySql($sql)->one();
    }
}
