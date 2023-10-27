<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class SwedishJFT extends JFT
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
        $data = HttpUtility::httpGet('https://www.nasverige.org/dagens-text/');
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
}
