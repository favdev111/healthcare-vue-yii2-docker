<?php

namespace modules\account\models\api\search;

use modules\account\models\api\AccountTeam;

class AccountTeamSearch extends AccountTeam
{
    public function rules()
    {
        return [
            [['accountId', 'teamId'], 'integer']
        ];
    }
}
