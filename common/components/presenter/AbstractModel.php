<?php

namespace common\components\presenter;

/**
 * Class AbstractModel
 * @package modules\account\components\presenter
 */
abstract class AbstractModel implements PresenterModelInterface
{
    /** @var array */
    protected $skipNullFields = [];

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $fields = get_object_vars($this);
        if (count($this->skipNullFields) > 0) {
            foreach ($this->skipNullFields as $skipNullField) {
                if (is_null($fields[$skipNullField])) {
                    unset($fields[$skipNullField]);
                }
            }
        }
        unset($fields['skipNullFields']);

        return $fields;
    }
}
