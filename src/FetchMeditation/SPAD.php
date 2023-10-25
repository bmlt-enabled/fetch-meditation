<?php

namespace FetchMeditation;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/SPADEntry.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SPAD {
    private string $outputType;
    public function __construct($settings = null)
    {
        $this->outputType = $settings->outputType;
    }

    public function fetch()
    {
        $data = $this->getData();
        $entry = new SPADEntry(
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
        $data = $this->getSpad();
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

    public function getSpad(): array
    {
        $jft_url = 'https://spadna.org';
        libxml_use_internal_errors(true);
        $data = $this->httpGet($jft_url);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $domDocument = new \DOMDocument();
        $domDocument->validateOnParse = true;
        $domDocument->loadHTML($data);
        $jftKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'divider', 'thought', 'copyright'];
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
        if (array_key_exists('divider', $result)) {
            unset($result['divider']);
        }
        $result["copyright"] = preg_replace('/\s+/', ' ', str_replace("\n", "", $result["copyright"]));

        return $result;
    }
}
