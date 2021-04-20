<?php

namespace api2\helpers\parser\adapter;

use PHPHtmlParser\Dom;

class NHSInformAdapter implements ParserAdapterInterface
{
    public const START_PAGE = 'https://www.nhsinform.scot/illnesses-and-conditions/a-to-z';

    public function parse(): \Generator
    {
        $dom = new Dom();
        $dom->load(self::START_PAGE);
        $content = $dom->find('.blockgrid-list a .module__title');
        foreach ($content as $item) {
            yield str_replace('&#39;', "'", trim(html_entity_decode($item->innerHtml)));
        }
    }
}
