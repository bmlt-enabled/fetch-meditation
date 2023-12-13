<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class ItalianJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://na-italia.org/get-jft');
        $data = json_decode($data, true)[0];
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' .  $data['content']);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        $paragraphs = $xpath->query('//p');

        $initialResult = [
            'quote' => '',
            'source' => '',
            'thought' => '',
            'copyright' => '',
            'page' => '',
        ];

        // Split the title and date parts
        $titleParts = explode(',', $data['title']);
        $initialResult['title'] = trim($titleParts[0]);
        $initialResult['date'] = trim(end($titleParts));

        // Populate the result array with paragraph content
        foreach ($paragraphs as $index => $paragraph) {
            $key = ($index === 0) ? 'quote' : ($index + 1);
            $initialResult[$key] = $paragraph->textContent;
        }

        $result = [
            'content' => [],
            'thought' => '',
            'source' => '',
        ];

        $lastNumericKey = null;
        $firstNumericKeyWithSource = null;

        // Iterate through the result array and categorize content
        foreach ($initialResult as $key => $value) {
            if (str_starts_with($value, '--')) {
                $firstNumericKeyWithSource = $key;
                $result['source'] = substr($value, 2);
            }
            if (is_numeric($key)) {
                $lastNumericKey = $key;
                $result['content'][$key] = $value;
                $result['thought'] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        // Remove unnecessary content entries
        unset($result['content'][$firstNumericKeyWithSource]);
        unset($result['content'][$lastNumericKey]);
        $result['content'] = array_values($result['content']);
        $quoteParts = explode('--', $result['quote']);
        $result['quote'] = $quoteParts[0] ?? '';
        $result['source'] =  $quoteParts[1] ?? $result['source'];
        $result = array_map(function ($item) {
            if (is_array($item)) {
                return array_map(function ($paragraph) {
                    return trim($paragraph, "\xC2\xA0");
                }, $item);
            } else {
                return trim($item, "\xC2\xA0");
            }
        }, $result);

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
