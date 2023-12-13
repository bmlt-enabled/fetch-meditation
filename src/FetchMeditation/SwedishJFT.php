<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class SwedishJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://www.nasverige.org/dagens-text/');
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXpath($doc);

        $result = [
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
            $result['date'] = trim($dateElement->item(0)->textContent);
        }

        // Extract the title
        $dateElement = $xpath->query('//div[@class="border-bottom mb-4"]/h2');
        if ($dateElement->length > 0) {
            $result['title'] = trim($dateElement->item(0)->textContent);
        }

        // Extract the quote and source
        $quoteElement = $xpath->query('//p[@class="bg-lightBlue p-4 preamble"]');
        if ($quoteElement->length > 0) {
            $result['quote'] = $quoteElement->item(0)->textContent;
            $result['quote'] = preg_replace('/\s+/', ' ', $result['quote']);
            $quoteParts = explode('/', $result['quote']);
            $result['quote'] = trim($quoteParts[0]);
            $result['source'] = trim(end($quoteParts));
        }

        // Extract the thought
        $thoughtElement = $xpath->query('//div[@class="col-12 col-md-8 col-lg-6 pt-5"]/p[2]');
        if ($thoughtElement->length > 0) {
            $result['thought'] = trim($thoughtElement->item(0)->textContent);
        }

        // Extract the content
        $resultElement = $xpath->query('//div[@class="col-12 col-md-8 col-lg-6 pt-5"]/p[1]');
        if ($thoughtElement->length > 0) {
            $items = explode("\n", trim($resultElement->item(0)->textContent));
            $result['content'] = array_filter($items, 'strlen');
        }

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
