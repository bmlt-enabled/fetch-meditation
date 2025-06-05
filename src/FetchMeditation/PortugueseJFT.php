<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class PortugueseJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://na-pt.org/sph/');
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

        // Extract date from qx-text-6112
        $dateElement = $xpath->query('//div[@id="qx-text-6112"]')->item(0);
        if ($dateElement) {
            $dateText = trim($dateElement->nodeValue);
            // Extract just the date part, removing "MEDITAÇÃO DO DIA"
            if (preg_match('/(\w+,\s*\d+\s+de\s+\w+\s+de\s+\d+)/', $dateText, $matches)) {
                $result['date'] = $matches[1];
            }
        }

        // Extract title from h2 within qx-heading-5146
        $titleElement = $xpath->query('//div[@id="qx-heading-5146"]//h2/span')->item(0);
        if ($titleElement) {
            $result['title'] = trim($titleElement->nodeValue);
        }

        // Extract main content from qx-text-39245
        $contentElement = $xpath->query('//div[@id="qx-text-39245"]')->item(0);
        if ($contentElement) {
            // Get the raw text content
            $fullText = trim($contentElement->textContent);

            // Extract the quote (text between quotes)
            if (preg_match('/"([^"]+)"/', $fullText, $matches)) {
                $result['quote'] = trim($matches[1]);
            }

            // Extract source (the line that comes immediately after the quote)
            $lines = explode("\n", $fullText);
            for ($i = 0; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                // If this line contains the quote, the next non-empty line is the source
                if (preg_match('/"[^"]*"/', $line)) {
                    for ($j = $i + 1; $j < count($lines); $j++) {
                        $nextLine = trim($lines[$j]);
                        if (!empty($nextLine)) {
                            $result['source'] = $nextLine;
                            break 2; // Break out of both loops
                        }
                    }
                }
            }

            // Extract the main content (everything after the source)
            $contentText = '';
            $sourceFound = false;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                // Skip the quote line
                if (preg_match('/^"[^"]*"/', $line)) {
                    continue;
                }

                // Skip the source line (whatever it is)
                if (!$sourceFound && $line === $result['source']) {
                    $sourceFound = true;
                    continue;
                }

                // Collect content after source
                if ($sourceFound) {
                    if (!empty($contentText)) {
                        $contentText .= ' ';
                    }
                    $contentText .= $line;
                }
            }

            if (!empty($contentText)) {
                $result['content'] = [trim(preg_replace('/\s+/u', ' ', $contentText))];
            }
        }

        // Extract "SÓ POR HOJE" section from qx-text-80201
        $sphElement = $xpath->query('//div[@id="qx-text-80201"]')->item(0);
        if ($sphElement) {
            $sphText = trim($sphElement->nodeValue);
            // Remove "SÓ POR HOJE:" prefix
            $result['thought'] = trim(preg_replace('/^SÓ POR HOJE:\s*/', '', $sphText));
            $result['thought'] = preg_replace('/\s+/', ' ', $result['thought']);
        }

        // Extract copyright from qx-text-28340
        $copyrightElement = $xpath->query('//div[@id="qx-text-28340"]')->item(0);
        if ($copyrightElement) {
            $result['copyright'] = trim(str_replace("\n", " ", $copyrightElement->nodeValue));
            $result['copyright'] = preg_replace('/\s+/', ' ', $result['copyright']);
        }

        $result['page'] = '';

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
