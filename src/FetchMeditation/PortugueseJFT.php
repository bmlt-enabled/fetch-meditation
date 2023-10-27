<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class PortugueseJFT extends JFT
{
    public function fetch()
    {
        $data = $this->getData();
        $entry = new JFTEntry(
            $data['date'],
            $data['title'],
            $data['page'],
            $data['quote'],
            $data['source'],
            $data['content'],
            $data['thought'],
            $data['copyright']
        );
        return $entry;
    }
    private function getData(): array
    {
        libxml_use_internal_errors(true);
        $data = HttpUtility::httpGet('https://www.na.org.br/meditacao/');
        $doc = new \DOMDocument();
        $doc->loadHTML($data);
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
                    $content['date'] = trim($element->nodeValue);
                    break;
                case 'cit':
                    $content['source'] = trim($element->nodeValue);
                    break;
                case 'ef':
                    $content['quote'] = trim($element->nodeValue);
                    break;
                case 'sph':
                    $content['thought'] = trim($element->nodeValue);
                    $content['thought'] = preg_replace('/\s+/', ' ', $content['thought']);
                    break;
                case 'ct':
                    $content['copyright'] = trim(str_replace("\n", "", $element->nodeValue));
                    $content['copyright'] = preg_replace('/\s+/', ' ', $content['copyright']);
                    break;
            }
        }

        $h1Elements = $doc->getElementsByTagName('h1');
        foreach ($h1Elements as $element) {
            $class = $element->getAttribute('class');
            if ($class == 'tit') {
                $content['title'] = trim($element->nodeValue);
                break;
            }
        }
        $content['page'] = '';
        $content['content'] = array_map('trim', $paragraphs);
        return $content;
    }
}
