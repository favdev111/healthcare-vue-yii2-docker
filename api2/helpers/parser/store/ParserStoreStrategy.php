<?php

namespace api2\helpers\parser\store;

interface ParserStoreStrategy
{
    public function batch($data);
    public function store($item);
}
