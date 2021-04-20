<?php

namespace api2\helpers\parser\adapter;

use PHPHtmlParser\Dom;
use yii\helpers\HtmlPurifier;

class MayoClinicAdapter implements ParserAdapterInterface
{
    public const START_PAGE = 'https://www.mayoclinic.org/diseases-conditions/index?letter=A';
    public const HOST_PAGE = 'https://www.mayoclinic.org';

    public function fillLinkList(): array
    {
        $dom = new Dom();
        $dom->load(file_get_contents(self::START_PAGE));
        $links = $dom->find('.acces-alpha a');
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
            $content = $dom->find('.index.content-within li a');
            foreach ($content as $item) {
                $itemContent = HtmlPurifier::process(trim(html_entity_decode($item->innerHtml)));
                if (false !== ($start = strpos($itemContent, '<span'))) {
                    $end = strpos($itemContent, 'span>');
                    $itemContent = substr_replace($itemContent, '', $start, $end - $start + 5);
                }
                yield $itemContent;
            }
        }
    }
}
