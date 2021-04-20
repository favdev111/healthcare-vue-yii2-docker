<?php

namespace modules\account\interfaces;

interface ChangeLogInterface
{
    //need to call in afterSave() model method
    public function changeLog(array $oldAttributes, string $additionalComment = '');
}
