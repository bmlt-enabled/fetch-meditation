<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class PortugueseJFT extends JFT
{
    public function getLanguage(): JFTLanguage
    {
        return $this->settings->language;
    }

    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet(JFTLanguage::Portuguese->url());
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        $firstElement = $xpath->query('//p[@class="cit"]')->item(0);
        $lastElement = $xpath->query('//p[@class="sph"]')->item(0);
        $contentResult = '';
        if ($firstElement && $lastElement) {
            $node = $firstElement->nextSibling;
            while ($node !== $lastElement) {
                if ($node->nodeType === XML_ELEMENT_NODE) {
                    $contentResult .= trim($doc->saveHTML($node));
                } elseif ($node->nodeType === XML_TEXT_NODE) {
                    $contentResult .= trim($node->nodeValue);
                }
                $node = $node->nextSibling;
            }
        }

        $contentResult = strip_tags($contentResult, ['br', 'em', 'i', 'b']);
        $paragraphs = explode('<br>', $contentResult);

        $fetchElements = $doc->getElementsByTagName('p');
        foreach ($fetchElements as $element) {
            $class = $element->getAttribute('class');
            switch ($class) {
                case 'dat':
                    $result['date'] = trim($element->nodeValue);
                    break;
                case 'cit':
                    $result['source'] = trim($element->nodeValue);
                    break;
                case 'ef':
                    $result['quote'] = trim($element->nodeValue);
                    break;
                case 'sph':
                    $result['thought'] = trim($element->nodeValue);
                    $result['thought'] = preg_replace('/\s+/', ' ', $result['thought']);
                    break;
                case 'ct':
                    $result['copyright'] = trim(str_replace("\n", "", $element->nodeValue));
                    $result['copyright'] = preg_replace('/\s+/', ' ', $result['copyright']);
                    break;
            }
        }

        $h1Elements = $doc->getElementsByTagName('h1');
        foreach ($h1Elements as $element) {
            $class = $element->getAttribute('class');
            if ($class == 'tit') {
                $result['title'] = trim($element->nodeValue);
                break;
            }
        }
        $result['page'] = '';
        $result['content'] = array_map(function ($paragraph) {
            return trim(preg_replace('/\s+/u', ' ', $paragraph));
        }, $paragraphs);

        return new JFTEntry(
            $result['date'],
            $result['title'],
            $result['page'],
            $result['quote'],
            $result['source'],
            $result['content'],
            $result['thought'],
            $result['copyright']
        );
    }
}
