<?php

namespace common\models;

use modules\account\models\Account;
use modules\account\models\search\ClientBalanceTransactionSearch;
use Yii;

class ClientBalancePdf extends Pdf
{
    public $clientId;
    public $countTransactions;

    const CLIENT_BALANCE_TRANSACTION_LIMIT = 200;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['clientId'], 'exist', 'targetClass' => Account::class, 'targetAttribute' => 'id'],
            [['countTransactions'], function () {
                if ($this->countTransactions > static::CLIENT_BALANCE_TRANSACTION_LIMIT) {
                    $this->addError('', 'Trying to download more than ' . static::CLIENT_BALANCE_TRANSACTION_LIMIT . ' transactions');
                } else if ($this->countTransactions == 0) {
                    $this->addError('', "Unable to download .pdf file. There are no transactions available");
                }
            }
            ]
        ]);
    }

    protected function prepareContent()
    {
        $client = Account::find()->where(['id' => $this->clientId])->limit(1)->one();
        $search = (new ClientBalanceTransactionSearch());
        $search->clientId = $this->clientId;
        $provider = $search->disablePagination(true)->useDefaultDateRanges(false)->search(Yii::$app->getRequest()->getQueryParams());
        $this->countTransactions = $provider->getTotalCount();
        $transaction = $provider->getModels();
        return Yii::$app->controller->renderPartial('@themes/basic/modules/account/views/common/client-balance-transaction/clientBalanceTransactionTable.php', ['client' => $client, 'transactions' => $transaction]);
    }

    public function getPdf()
    {
        $this->content = $this->prepareContent();
        return parent::getPdf();
    }
}
