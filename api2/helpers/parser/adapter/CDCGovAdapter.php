<?php

namespace api2\helpers\parser\adapter;

use PHPHtmlParser\Dom;
use yii\helpers\HtmlPurifier;

class CDCGovAdapter implements ParserAdapterInterface
{
    public const START_PAGE = 'https://www.cdc.gov/diseasesconditions/az/a.html';
    public const HOST_PAGE = 'https://www.cdc.gov';

    public function fillLinkList(): array
    {
        $dom = new Dom();
        $dom->load(file_get_contents(self::START_PAGE));
        $links = $dom->find('.az-strip a');
        $hrefs = [];
        foreach ($links as $link) {
            $value = $link->getAttribute('href');
            $hrefs[md5($value)] = $value;
        }
        return $hrefs;
    }

    public function parse(): \Generator
    {
        $linkList = $this->fillLinkList();
        foreach ($linkList as $link) {
            $dom = new Dom();
            $dom->load(self::HOST_PAGE . $link);
            $content = $dom->find('.az-content li a');
            foreach ($content as $item) {
                $itemContent = HtmlPurifier::process(trim(html_entity_decode($item->innerHtml)));
                $itemContent = str_replace('<em>', '', $itemContent);
                $itemContent = str_replace('</em>', '', $itemContent);
                yield $itemContent;
            }
        }
    }
}
