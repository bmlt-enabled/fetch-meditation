<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class GermanSPAD extends SPAD
{
    /**
     * Helper function to clean text - removes newlines and normalizes spaces
     */
    private function cleanText(string $text): string
    {
        // Remove newlines
        $text = str_replace("\n", "", $text);
        // Replace multiple spaces with a single space
        $text = preg_replace('/\s+/', ' ', $text);
        // Trim leading and trailing spaces
        return trim($text);
    }

    public function fetch(): SPADEntry
    {
        $data = HttpUtility::httpGet('https://narcotics-anonymous.de/artikel/nur-fuer-heute/');
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' .  $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        // Find the SPAD tab content
        $spadTabContent = $xpath->query('//div[@id="tab-id-2-content"]')->item(0);
        if (!$spadTabContent) {
            throw new \Exception("SPAD tab content not found");
        }

        // Find the SPAD container inside the tab content
        $container = $xpath->query('.//div[@id="jft-container"]', $spadTabContent)->item(0);
        if (!$container) {
            throw new \Exception("SPAD container not found");
        }

        $elements = array();

        // Loop through the child nodes of the container
        foreach ($container->childNodes as $node) {
            // Check if the node is an element
            if ($node->nodeType === XML_ELEMENT_NODE) {
                // Get the ID of the element
                $id = $node->getAttribute('id');
                // Check if the ID is "jft-content"
                if ($id === 'jft-content') {
                    // Initialize an empty sub-array for "jft-content"
                    $contentArray = array();
                    // Loop through the child nodes of "jft-content"
                    foreach ($node->childNodes as $contentNode) {
                        // Check if the child node is a <p> element
                        if ($contentNode->nodeName === 'p') {
                            // Add the <p> element's content to the sub-array
                            $contentArray[] = $this->cleanText($contentNode->textContent);
                        }
                    }
                    // Add the sub-array to the main array with "jft-content" as the key
                    $elements[$id] = $contentArray;
                } else {
                    // Add other elements to the array with their IDs as keys
                    $elements[$id] = $this->cleanText($node->textContent);
                }
            }
        }

        // Set default values for missing elements
        $elements['page'] = '';
        $elements['copyright'] = 'Copyright © ' . date('Y') . ' by Narcotics Anonymous World Services, Inc. Alle Rechte vorbehalten.';

        // Clean up the content array
        $elements['jft-content'] = array_values(array_filter($elements['jft-content']));

        return new SPADEntry(
            $elements['jft-date'],
            $elements['jft-title'],
            $elements['page'],
            $elements['jft-quote'],
            $elements['jft-quote-source'],
            $elements['jft-content'],
            $elements['jft-thought'],
            $elements['copyright']
        );
    }
}
