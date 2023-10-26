<?php

namespace FetchMeditation;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/JFTEntry.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class JFT
{
    private string $language;
    public function __construct($settings = null)
    {
        $this->language = $settings->language ?? 'en';
    }

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
        return match ($this->language) {
            'en' => $this->getEnglish(),
            'es' => $this->getSpanish(),
            'fr' => $this->getFrench(),
            'it' => $this->getItalian(),
            'ja' => $this->getJapanese(),
            'pt' => $this->getPortuguese(),
            'ru' => $this->getRussian(),
            'sv' => $this->getSwedish(),
            default => [],
        };
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    private function httpGet(string $url): string
    {
        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0',
                ],
            ];
            $response = $client->get($url, $options);
            if (in_array($response->getStatusCode(), [200, 302, 304])) {
                return $response->getBody()->getContents();
            } else {
                return 'Received non-acceptable status code: ' . $response->getStatusCode();
            }
        } catch (RequestException $e) {
            return 'Guzzle RequestException: ' . $e->getMessage();
        } catch (\Exception $e) {
            return 'An error occurred: ' . $e->getMessage();
        }
    }

    private function getEnglish(): array
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://www.jftna.org/jft/');
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' .  $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $jftKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'thought', 'copyright'];
        $result = [];
        foreach ($doc->getElementsByTagName('td') as $i => $td) {
            if ($jftKeys[$i] === 'content') {
                $innerHTML = '';
                foreach ($td->childNodes as $child) {
                    $innerHTML .= $td->ownerDocument->saveHTML($child);
                }
                $result['content'] = preg_split('/<br\s*\/?>/', trim($innerHTML), -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $result[$jftKeys[$i]] = trim($td->nodeValue);
            }
        }
        $result["copyright"] = preg_replace('/\s+/', ' ', str_replace("\n", "", $result["copyright"]));
        return $result;
    }

    private function getSpanish()
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://forozonalatino.org/wp-content/uploads/meditaciones/' . static::getTimezoneDate('spanish', 'm/d') . '.html');
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="ISO-8859-1">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        // Get content
        $paragraphs = [];
        for ($i = 1; $i <= 3; $i++) {
            $query = "//p[preceding-sibling::comment()[contains(., 'PARRAFO $i')]]";
            $paragraphNodes = $xpath->query($query);

            if ($paragraphNodes->length > 0) {
                $paragraph = trim($paragraphNodes->item(0)->textContent);
                $paragraphs[] = $paragraph;
            }
        }

        // Get Thought
        $extractedThought = '';
        $startComment = $xpath->query('//comment()[contains(., "SOLO X HOY insertar AQUI sin el Solo por Hoy")]')->item(0);
        $endComment = $xpath->query('//comment()[contains(., "FIN SOLO X HOY")]')->item(0);

        if ($startComment && $endComment) {
            $startNode = $startComment->nextSibling;
            while ($startNode && $startNode !== $endComment) {
                $extractedThought .= $doc->saveHTML($startNode);
                $startNode = $startNode->nextSibling;
            }
        }

        $content = [];
        $fetchElements = $doc->getElementsByTagName('p');
        foreach ($fetchElements as $element) {
            $class = $element->getAttribute('class');
            switch ($class) {
                case 'fecha-sxh':
                    $content['date'] = $element->nodeValue;
                    break;
                case 'titulo-sxh':
                    $content['title'] = $element->nodeValue;
                    break;
                case 'descripcion-sxh':
                    $content['quote'] = $element->nodeValue;
                    break;
                case 'numero-pagina-sxh':
                    $content['source'] = $element->nodeValue;
                    break;
                case 'soloxhoycontainer':
                    $content['thought'] = $element->nodeValue;
                    break;
            }
        }

        $content['content'] = $paragraphs;
        $content['page'] = '';
        $content['copyright'] = 'Servicio del Foro Zonal Latinoamericano, Copyright 2017 NA World Services, Inc. Todos los Derechos Reservados.';
        $content['thought']  = 'Sólo por Hoy: ' . trim($extractedThought);

        return $content;
    }

    private function getPortuguese()
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://www.na.org.br/meditacao/');
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
        ;
        return $content;
    }

    private function getItalian()
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://na-italia.org/get-jft');
        $data = json_decode($data, true)[0];
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' .  $data['content']);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        $paragraphs = $xpath->query('//p');

        $result = [
            'quote' => '',
            'source' => '',
            'thought' => '',
            'copyright' => '',
            'page' => '',
        ];

        // Split the title and date parts
        $titleParts = explode(',', $data['title']);
        $result['title'] = trim($titleParts[0]);
        $result['date'] = trim(end($titleParts));

        // Populate the result array with paragraph content
        foreach ($paragraphs as $index => $paragraph) {
            $key = ($index === 0) ? 'quote' : ($index + 1);
            $result[$key] = $paragraph->textContent;
        }

        $resultArray = [
            'content' => [],
            'thought' => '',
            'source' => '',
        ];

        $lastNumericKey = null;
        $firstNumericKeyWithSource = null;

        // Iterate through the result array and categorize content
        foreach ($result as $key => $value) {
            if (str_starts_with($value, '--')) {
                $firstNumericKeyWithSource = $key;
                $resultArray['source'] = substr($value, 2);
            }
            if (is_numeric($key)) {
                $lastNumericKey = $key;
                $resultArray['content'][$key] = $value;
                $resultArray['thought'] = $value;
            } else {
                $resultArray[$key] = $value;
            }
        }

        // Remove unnecessary content entries
        unset($resultArray['content'][$firstNumericKeyWithSource]);
        unset($resultArray['content'][$lastNumericKey]);
        $resultArray['content'] = array_values($resultArray['content']);
        $quoteParts = explode('--', $resultArray['quote']);
        $resultArray['quote'] = $quoteParts[0] ?? '';
        $resultArray['source'] =  $quoteParts[1] ?? $resultArray['source'];
        return $resultArray;
    }

    private function getRussian()
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://na-russia.org/eg');
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' .  $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $jftKeys = ['date', 'title', 'quote', 'source', 'content', 'thought', 'page'];
        $result = [];
        foreach ($doc->getElementsByTagName('td') as $i => $td) {
            if ($jftKeys[$i] === 'content') {
                $innerHTML = '';
                foreach ($td->childNodes as $child) {
                    $innerHTML .= $td->ownerDocument->saveHTML($child);
                }
                $result['content'] = preg_split('/<br\s*\/?>/', trim($innerHTML), -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $result[$jftKeys[$i]] = trim($td->nodeValue);
            }
        }
        $result['copyright'] = '';
        return $result;
    }

    private function getSwedish(): array
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://www.nasverige.org/dagens-text/');
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXpath($doc);

        $content = [
            'date' => '',
            'quote' => '',
            'source' => '',
            'thought' => '',
            'content' => [],
            'title' => '',
            'page' => '',
            'copyright' => 'Copyright (c) ' . date("Y") . ', NA World Service, Inc. All Rights Reserved',
        ];

        // Extract the date
        $dateElement = $xpath->query('//div[@class="border-bottom mb-4"]/p[@class="h3"]');
        if ($dateElement->length > 0) {
            $content['date'] = trim($dateElement->item(0)->textContent);
        }

        // Extract the title
        $dateElement = $xpath->query('//div[@class="border-bottom mb-4"]/h2');
        if ($dateElement->length > 0) {
            $content['title'] = trim($dateElement->item(0)->textContent);
        }

        // Extract the quote and source
        $quoteElement = $xpath->query('//p[@class="bg-lightBlue p-4 preamble"]');
        if ($quoteElement->length > 0) {
            $content['quote'] = $quoteElement->item(0)->textContent;
            $content['quote'] = preg_replace('/\s+/', ' ', $content['quote']);
            $quoteParts = explode('/', $content['quote']);
            $content['quote'] = trim($quoteParts[0]);
            $content['source'] = trim(end($quoteParts));
        }

        // Extract the thought
        $thoughtElement = $xpath->query('//div[@class="col-12 col-md-8 col-lg-6 pt-5"]/p[2]');
        if ($thoughtElement->length > 0) {
            $content['thought'] = trim($thoughtElement->item(0)->textContent);
        }

        // Extract the content
        $contentElement = $xpath->query('//div[@class="col-12 col-md-8 col-lg-6 pt-5"]/p[1]');
        if ($thoughtElement->length > 0) {
            $items = explode("\n", trim($contentElement->item(0)->textContent));
            $content['content'] = array_filter($items, 'strlen');
        }
        return $content;
    }

    private function getFrench(): array
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://jpa.narcotiquesanonymes.org/');
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXpath($doc);

        $content = [
            'date' => '',
            'quote' => '',
            'source' => '',
            'thought' => '',
            'content' => [],
            'title' => '',
            'page' => '',
            'copyright' => 'Copyright (c) ' . date("Y") . ', NA World Service, Inc. All Rights Reserved',
        ];

        // Extract the title
        $titleElement = $xpath->query('//div[@class="cartouche"]/h1');
        if ($titleElement->length > 0) {
            $content['title'] = trim($titleElement->item(0)->textContent);
        }

        // Extract the date
        $dateElement = $xpath->query('//p[@class="info-publi"]');
        if ($dateElement->length > 0) {
            $content['date'] = trim($dateElement->item(0)->textContent);
        }

        // Extract the quote and source
        $quoteDiv = $xpath->query('//div[@class="chapo"]')->item(0);
        $quotePTags = $quoteDiv->getElementsByTagName('p');
        if ($quotePTags->length >= 2) {
            $content['quote'] = trim($quotePTags->item(0)->textContent);
            $content['source'] = trim($quotePTags->item(1)->textContent);
        }

        // Extract the thought
        $thoughtElement = $xpath->query('//h3[@class="h3 spip"]');
        if ($thoughtElement->length > 0) {
            $content['thought'] = trim($thoughtElement->item(0)->textContent);
        }

        // Extract the content
        $textDiv = $xpath->query('//div[@class="texte"]');
        if ($textDiv->length > 0) {
            $content['content'] = [];
            $pTags = $textDiv->item(0)->getElementsByTagName('p');
            foreach ($pTags as $pTag) {
                $content['content'][] = trim($pTag->textContent);
            }
        }
        return $content;
    }

    private function getJapanese(): array
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://najapan.org/just_for_today/');
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXpath($doc);

        $content = [
            'date' => '',
            'quote' => '',
            'source' => '',
            'thought' => '',
            'content' => [],
            'title' => '',
            'page' => '',
            'copyright' => 'Copyright (c) ' . date("Y") . ', NA World Service, Inc. All Rights Reserved',
        ];

        // Extract the date and title
        $h1Element = $xpath->query('//h2')->item(0);
        if ($h1Element !== null) {
            $dateParts = explode("　", $h1Element->textContent);
            $content['date'] = trim($dateParts[0]);
            $content['title'] = trim(end($dateParts));
        }

        // Extract quote
        $p0Element = $xpath->query('//p')->item(0);
        if ($p0Element !== null) {
            $content['quote'] = trim($p0Element->textContent);
        }

        // Extract the source and page
        $p1Element = $xpath->query('//p')->item(1);
        if ($p1Element !== null) {
            $sourceParts = explode("　", $p1Element->textContent);
            $content['source'] = trim($sourceParts[0]);
            $content['page'] = count($sourceParts) > 0 ? trim(end($sourceParts)) : '';
        }

        $pTags = $doc->getElementsByTagName('p');
        $pTagCount = $pTags->length;

        // Extract the thought
        if ($pTagCount >= 2) {
            $thoughtPTag = $pTags->item($pTagCount - 2); // Get the second to last <p> tag
            $content['thought'] = trim($thoughtPTag->textContent);
        }

        // Extract the copyright
        $centerTags = $doc->getElementsByTagName('center');
        if ($centerTags->length >= 0) {
            $content['copyright'] = trim($centerTags->item(0)->textContent);
        }

        // Extract the content
        $rightAlignedP = $xpath->query('//p[@style="text-align:right"]')->item(0);
        if ($rightAlignedP) {
            $paragraphs = [];
            $nextNode = $rightAlignedP->nextSibling;
            while ($nextNode) {
                if ($nextNode->nodeName === 'p' && $nextNode->getElementsByTagName('b')->length > 0) {
                    break; // Stop when next <p> with <b> tag is found
                }
                $paragraphs[] .= trim($nextNode->textContent);
                $nextNode = $nextNode->nextSibling;
            }
            $content['content'] = array_values(array_filter($paragraphs));
        }
        return $content;
    }

    private static function getTimezoneDate(string $language, $format = 'md'): string
    {
        $timezoneMap = [
            'german' => 'Europe/Berlin',
            'swedish' => 'Europe/Stockholm',
            'danish' => 'Europe/Copenhagen',
            'italian' => 'Europe/Rome',
            'spanish' => 'America/Mexico_City',
        ];

        $timezone = array_key_exists($language, $timezoneMap) ? new \DateTimeZone($timezoneMap[$language]) : new \DateTimeZone('America/New_York');
        $date = new \DateTime('now', $timezone);
        return $date->format($format);
    }
}
