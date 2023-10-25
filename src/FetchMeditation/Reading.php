<?php

namespace FetchMeditation;
include 'HttpClient.php';

class Reading
{
    public function get(array $config)
    {

        $get = new HttpClient();
        $data = $get->get("https://spadna.org");
        $content = $this->getSpadHtml($data);
        return $content;
    }

    public function getSpadHtml($data)
    {
        $domDoc = $this->createDomDocument($data);
        $xpath = new \DOMXpath($domDoc);
        $body = $xpath->query("//table");
        $reading = new \DOMDocument();
        foreach ($body as $child) {
            $reading->appendChild($reading->importNode($child, true));
        }
        return $reading->saveHTML();
    }

    protected function createDomDocument(string $data): \DOMDocument
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        return $dom;
    }
}
