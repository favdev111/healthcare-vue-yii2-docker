<?php

namespace api2\helpers\parser\adapter;

interface ParserAdapterInterface
{
    public function parse(): \Generator;
}
