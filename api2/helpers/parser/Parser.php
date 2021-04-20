<?php

namespace api2\helpers\parser;

use api2\helpers\parser\adapter\ParserAdapterInterface;
use api2\helpers\parser\store\ParserStoreStrategy;

class Parser
{
    public function parseAndStore(ParserAdapterInterface $adapter, ParserStoreStrategy $storeStrategy)
    {
        $storeStrategy->batch($adapter->parse());
    }
}
