<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class PortuguesePTJFT extends JFT
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
            // Get HTML content and convert <br> to newlines
            $htmlContent = $doc->saveHTML($contentElement);
            // Replace <br> tags with newlines before extracting text
            $htmlContent = preg_replace('/<br\s*\/?>/', "\n", $htmlContent);
            // Now create a new DOM element to extract clean text
            $tempDoc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $tempDoc->loadHTML('<?xml encoding="UTF-8">' . $htmlContent);
            libxml_clear_errors();
            libxml_use_internal_errors(false);
            $fullText = trim($tempDoc->textContent);

            // Extract the quote (text between quotes) - may span multiple lines
            if (preg_match('/"([^"]+)"/s', $fullText, $matches)) {
                $result['quote'] = trim(preg_replace('/\s+/', ' ', $matches[1]));
            }

            // Split into lines
            $lines = explode("\n", $fullText);

            // Find where the quote ends and extract source
            $quoteEndIndex = -1;
            for ($i = 0; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                // Look for line with closing quote
                if (preg_match('/"[^"]*"/', $line) && strpos($line, '"') !== false) {
                    $quoteEndIndex = $i;
                    // Get the next non-empty line as source
                    for ($j = $i + 1; $j < count($lines); $j++) {
                        $nextLine = trim($lines[$j]);
                        if (!empty($nextLine) && !preg_match('/^"/', $nextLine)) {
                            $result['source'] = $nextLine;
                            break 2;
                        }
                    }
                }
            }

            // Extract the main content (everything after the source)
            $contentText = '';
            $sourceLineIndex = -1;

            // Find the source line index
            for ($i = 0; $i < count($lines); $i++) {
                if (trim($lines[$i]) === $result['source']) {
                    $sourceLineIndex = $i;
                    break;
                }
            }

            // Collect all content after source line
            if ($sourceLineIndex >= 0) {
                for ($i = $sourceLineIndex + 1; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) {
                        continue;
                    }
                    // Skip any additional quotes at the start of content
                    if (strpos($line, '"') === 0 && preg_match('/^"[^"]+"\.$/', $line)) {
                        continue;
                    }
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
