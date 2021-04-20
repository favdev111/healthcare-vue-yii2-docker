<?php

namespace common\components\app;

trait CheckAppTrait
{
    public function isBackendApp()
    {
        return $this instanceof BackendApplication;
    }

    public function isApiApp()
    {
        return $this instanceof ApiApplication;
    }

    public function isApi2App()
    {
        return $this instanceof Api2Application;
    }
}
