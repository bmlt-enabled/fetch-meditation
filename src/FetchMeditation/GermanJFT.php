<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class GermanJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://narcotics-anonymous.de/artikel/nur-fuer-heute/');
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' .  $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);
        $container = $xpath->query('//div[@id="meditation-container"]')->item(0);
        // Find the div with id "meditation-container"
        $elements = [];

        // Loop through the child nodes of the container
        foreach ($container->childNodes as $node) {
            // Check if the node is an element
            if ($node->nodeType === XML_ELEMENT_NODE) {
                // Get the ID of the element
                $id = $node->getAttribute('id');
                // Check if the ID is "meditation-content"
                if ($id === 'meditation-content') {
                    // Initialize an empty sub-array for "meditation-content"
                    $contentArray = [];
                    // Loop through the child nodes of "meditation-content"
                    foreach ($node->childNodes as $contentNode) {
                        // Check if the child node is a <p> element
                        if ($contentNode->nodeName === 'p') {
                            // Add the <p> element's content to the sub-array
                            $contentArray[] = str_replace("\n", "", trim($contentNode->nodeValue));
                        }
                    }
                    // Add the sub-array to the main array with "meditation-content" as the key
                    $elements[$id] = $contentArray;
                } else {
                    // Add other elements to the array with their IDs as keys
                    $elements[$id] = str_replace("\n", "", $node->nodeValue);
                }
            }
        }
        $elements['page'] = '';
        $elements['copyright'] = '';
        $elements['meditation-content'] = array_values(array_filter($elements['meditation-content']));
        return new JFTEntry(
            $elements['meditation-date'],
            $elements['meditation-title'],
            $elements['page'],
            $elements['meditation-quote'],
            $elements['meditation-quote-source'],
            $elements['meditation-content'],
            $elements['meditation-thought'],
            $elements['copyright']
        );
    }
}
