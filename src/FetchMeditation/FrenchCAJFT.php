<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class FrenchCAJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://naquebec.org/juste-pour-aujourdhui/');
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
            'copyright' => 'Copyright (c) ' . date("Y") . ', NA World Services, Inc. All Rights Reserved',
        ];

        // The meditation is rendered inside a single Elementor loop item, which
        // scopes our queries away from the rest of the page (header, footer, etc.)
        $item = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " e-loop-item ")]')->item(0);
        if ($item === null) {
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

        // Extract the title (the day's heading, e.g. "Avoir raison")
        $titleElement = $xpath->query('.//h1[contains(@class, "elementor-heading-title")]', $item);
        if ($titleElement->length > 0) {
            $result['title'] = trim($titleElement->item(0)->textContent);
        }

        // The two text-editor widgets hold the date followed by the italic quote
        $editors = $xpath->query('.//div[contains(@class, "elementor-widget-text-editor")]', $item);
        if ($editors->length >= 1) {
            $result['date'] = trim($editors->item(0)->textContent);
        }
        if ($editors->length >= 2) {
            $result['quote'] = trim($editors->item(1)->textContent);
        }

        // The post content holds the source citation, the body paragraphs and the
        // closing "Juste pour aujourd'hui" thought, in that order.
        $paragraphs = $xpath->query('.//div[contains(@class, "elementor-widget-theme-post-content")]//p', $item);
        $texts = [];
        foreach ($paragraphs as $paragraph) {
            $text = trim($paragraph->textContent);
            if ($text !== '') {
                $texts[] = $text;
            }
        }
        if (count($texts) >= 1) {
            // First paragraph is the source (e.g. "Texte de base, p. 72")
            $result['source'] = array_shift($texts);
        }
        if (count($texts) >= 1) {
            // Last paragraph is the closing thought
            $result['thought'] = array_pop($texts);
        }
        // Whatever remains in between is the meditation body
        $result['content'] = $texts;

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
