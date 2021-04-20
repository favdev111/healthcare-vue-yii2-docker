<?php

namespace modules\account\models\SubjectOrCategory;

use modules\account\models\AccountSubject;

/**
 * Class AccountSubjectOrCategory
 * @package modules\account\models\SubjectOrCategory
 * @param integer isCategory
 */
class AccountSubjectOrCategory extends AccountSubject
{
    use SubjectOrCategoryTrait;
}
