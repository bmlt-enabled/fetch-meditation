<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class FrenchJFT extends JFT
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

    public function getLanguage(): JFTLanguage
    {
        return $this->settings->language;
    }

    private function getData(): array
    {
        libxml_use_internal_errors(true);
        $data = HttpUtility::httpGet('https://jpa.narcotiquesanonymes.org/');
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
}
