<?php

namespace common\components\presenter;

/**
 * Interface PresenterModelInterface
 * @package modules\account\components\presenter
 */
interface PresenterModelInterface
{
    /**
     * @return array
     */
    public function toArray(): array;
}
