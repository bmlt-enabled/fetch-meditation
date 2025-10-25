<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class RussianJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://na-russia.org/meditation-today');
        $result = [
            'date' => '',
            'quote' => '',
            'source' => '',
            'thought' => '',
            'content' => [],
            'title' => '',
            'page' => '',
            'copyright' => '',
        ];

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        $this->extractFromDOM($xpath, $doc, $result);

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

    private function extractFromDOM(\DOMXPath $xpath, \DOMDocument $doc, array &$result): void
    {
        // Extract date - it has both text-secondary-blue and whitespace-nowrap classes and data-qa attribute
        $dateNodes = $xpath->query('//div[contains(@class, "text-secondary-blue") and contains(@class, "whitespace-nowrap") and @data-qa="meditation-date"]');
        if ($dateNodes->length > 0) {
            $result['date'] = trim($dateNodes->item(0)->textContent);
        }

        // Extract title (e.g., "Честная молитва")
        $titleNodes = $xpath->query('//div[contains(@class, "font-bold")]');
        if ($titleNodes->length > 0) {
            $result['title'] = trim($titleNodes->item(0)->textContent);
        }

        // Extract quote (text in italics)
        $quoteNodes = $xpath->query('//div[contains(@class, "italic")]');
        if ($quoteNodes->length > 0) {
            $result['quote'] = trim($quoteNodes->item(0)->textContent);
        }

        // Extract source - it has text-secondary-blue but NOT whitespace-nowrap
        $sourceNodes = $xpath->query('//div[contains(@class, "text-secondary-blue") and contains(@class, "min-w-0") and contains(@class, "break-words")]');
        if ($sourceNodes->length > 0) {
            $result['source'] = trim($sourceNodes->item(0)->textContent);
        }

        // Extract main content
        $contentNodes = $xpath->query('//div[contains(@class, "mt-8") and contains(@class, "text-md")]');
        if ($contentNodes->length > 0) {
            $contentHtml = $doc->saveHTML($contentNodes->item(0));

            // Split by <br> tags and filter out the "ТОЛЬКО СЕГОДНЯ:" part
            $paragraphs = preg_split('/<br\s*\/?>/i', $contentHtml);
            $filteredContent = [];

            foreach ($paragraphs as $paragraph) {
                $cleanText = trim(strip_tags($paragraph));
                if (!empty($cleanText) && !preg_match('/^ТОЛЬКО СЕГОДНЯ:/u', $cleanText)) {
                    $filteredContent[] = $cleanText;
                }
            }
            $result['content'] = $filteredContent;

            // Extract "ТОЛЬКО СЕГОДНЯ" section
            foreach ($paragraphs as $paragraph) {
                $cleanText = trim(strip_tags($paragraph));
                if (preg_match('/^ТОЛЬКО СЕГОДНЯ:\s*(.+)/u', $cleanText, $matches)) {
                    $result['thought'] = trim($matches[1]);
                    break;
                }
            }
        }
    }
}
