<?php

namespace FetchMeditation;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/SPADEntry.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SPAD
{
    private string $language;
    public function __construct($settings = null)
    {
        $this->language = $settings->language;
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

    private function getData(): array
    {
        return $this->getSpad();
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

    private function getSpad(): array
    {
        libxml_use_internal_errors(true);
        $data = $this->httpGet('https://spadna.org');
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $doc = new \DOMDocument();
        $doc->loadHTML($data);
        $spadKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'divider', 'thought', 'copyright'];
        $result = [];
        foreach ($doc->getElementsByTagName('td') as $i => $td) {
            $nodeValue = trim($td->nodeValue);
            if ($spadKeys[$i] === 'content') {
                $innerHTML = '';
                foreach ($td->childNodes as $child) {
                    $innerHTML .= $td->ownerDocument->saveHTML($child);
                }
                $result['content'] = array_map('trim', preg_split('/<br\s*\/?>/', $innerHTML, -1, PREG_SPLIT_NO_EMPTY));
            } elseif ($spadKeys[$i] !== 'divider') {
                $result[$spadKeys[$i]] = $nodeValue;
            }
        }
        $result["copyright"] = preg_replace('/\s+|\n/', ' ', $result["copyright"]);
        return $result;
    }
}
