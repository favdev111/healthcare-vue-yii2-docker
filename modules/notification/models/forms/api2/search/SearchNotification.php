<?php

namespace modules\notification\models\forms\api2\search;

use api2\components\models\forms\ApiBaseForm;
use modules\account\models\api2\Account;
use modules\notification\activeQuery\api2\NotificationQuery;
use modules\notification\models\entities\api2\Notification;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

/**
 * Class SearchNotification
 * @package modules\notification\models\forms\api2\search
 *
 * @property-read int $unreadCount
 * @property-read \modules\notification\activeQuery\api2\NotificationQuery $query
 */
class SearchNotification extends ApiBaseForm
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * SearchNotification constructor.
     * @param Account $account
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(Account $account, $config = [])
    {
        if ($account->isNewRecord) {
            throw new InvalidConfigException('Account must existing record');
        }
        $this->account = $account;
        parent::__construct($config);
    }

    /**
     * @return NotificationQuery
     */
    protected function getQuery(): NotificationQuery
    {
        return Notification::find()
            ->notifiableAccount()
            ->andWhere(['notifiable_id' => $this->account->id]);
    }

    /**
     * @return int
     */
    public function getUnreadCount(): int
    {
        return $this->query->unread()->count();
    }

    /**
     * @param array $params
     * @return string
     */
    public function search(array $params = [])
    {
        $query = $this->query->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);

        $this->load($params);

        return $dataProvider;
    }
}
