<?php

namespace common\components;

class Pagination extends \yii\data\Pagination
{
    public function getOffset()
    {
        $pageSize = $this->getPageSize();

        return $pageSize < 1 ? 0 : $this->getPage() * $pageSize - 3;  //hack to include in the collection of cut out tutors
    }
}
