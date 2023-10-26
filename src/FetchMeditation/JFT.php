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
        $this->language = $settings->language;
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
            'pt' => $this->getPortuguese(),
            'it' => $this->getItalian(),
            'ru' => $this->getRussian(),
            default => [],
        };
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
