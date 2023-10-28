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
        $data = HttpUtility::httpGet('https://www.na.org.br/meditacao/');
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        $paragraphs = [];
        // Find all <br> elements (line breaks)
        $brElements = $xpath->query('//br');

        // Loop through the line breaks to collect paragraphs
        $currentParagraph = '';
        foreach ($brElements as $brElement) {
            $previousNode = $brElement->previousSibling;

            // Collect text nodes until a new line break is encountered
            while ($previousNode !== null && $previousNode->nodeName !== 'br') {
                if ($previousNode->nodeType === XML_TEXT_NODE) {
                    $currentParagraph = $previousNode->nodeValue . $currentParagraph;
                }
                $previousNode = $previousNode->previousSibling;
            }

            // Add the collected paragraph to array and reset the current paragraph
            if (!empty($currentParagraph)) {
                $paragraphs[] = trim($currentParagraph);
                $currentParagraph = '';
            }
        }
        $paragraphs = array_values(array_filter($paragraphs));
        array_pop($paragraphs);

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
        $result['content'] = array_map('trim', $paragraphs);

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
