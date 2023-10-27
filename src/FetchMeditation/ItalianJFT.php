<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class ItalianJFT extends JFT
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
    private function getData(): array
    {
        libxml_use_internal_errors(true);
        $data = HttpUtility::httpGet('https://na-italia.org/get-jft');
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
}
