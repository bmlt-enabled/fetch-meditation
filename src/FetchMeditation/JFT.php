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

    public function getData(): array
    {
        $data = [];
        if ($this->language == 'en') {
            $data = $this->getEnglish();
        }
        if ($this->language == 'es') {
            $data = $this->getSpanish();
        }
        return $data;
    }

    public function httpGet(string $url): string
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

    public function getEnglish(): array
    {
        $jft_url = 'https://www.jftna.org/jft/';
        libxml_use_internal_errors(true);
        $data = $this->httpGet($jft_url);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $domDocument = new \DOMDocument();
        $domDocument->validateOnParse = true;
        $domDocument->loadHTML($data);
        $jftKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'thought', 'copyright'];
        $result = [];
        $xpath = new \DOMXPath($domDocument);
        foreach ($domDocument->getElementsByTagName('tr') as $i => $element) {
            $formattedElement = trim($element->nodeValue);
            if ($i == 5) {
                $values = [];
                foreach ($xpath->query('//tr') as $row) {
                    $rowValues = [];
                    foreach ($xpath->query('td', $row) as $cell) {
                        $innerHTML = '';
                        $children = $cell->childNodes;
                        foreach ($children as $child) {
                            $innerHTML .= $child->ownerDocument->saveXML($child);
                        }
                        $rowValues[] = $innerHTML;
                    }
                    $values[] = $rowValues;
                }
                $content = preg_split('/<br\s*\/?>/', $values[5][0], -1, PREG_SPLIT_NO_EMPTY);
                $result["content"] = array_map('trim', $content);
            } else {
                $result[$jftKeys[$i]] = $formattedElement;
            }
        }
        $result["copyright"] = preg_replace('/\s+/', ' ', str_replace("\n", "", $result["copyright"]));

        return $result;
    }

    public function getSpanish()
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet("https://forozonalatino.org/wp-content/uploads/meditaciones/10/25.html");
        $doc = new \DOMDocument();
        $doc->loadHTML($data);
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
        $fechaElements = $doc->getElementsByTagName('p');
        foreach ($fechaElements as $element) {
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
}
