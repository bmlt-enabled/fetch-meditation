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

            // Split into lines to look for quote pattern
            $lines = explode("\n", $fullText);
            $contentStartIndex = 0;
            $sourceLineIndex = -1;

            // Look for opening quote pattern: line ending with " followed by source line
            for ($i = 0; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                // Check if line ends with opening quote
                if (preg_match('/"\s*$/', $line)) {
                    // Extract quoted text (everything before the quote mark)
                    $result['quote'] = preg_replace('/\s*"\s*$/', '', $line);

                    // Check next line for source
                    if ($i + 1 < count($lines)) {
                        $nextLine = trim($lines[$i + 1]);
                        if (!empty($nextLine)) {
                            $result['source'] = $nextLine;
                            $sourceLineIndex = $i + 1;
                        }
                    }

                    $contentStartIndex = $sourceLineIndex >= 0 ? $sourceLineIndex + 1 : $i + 1;
                    break;
                }
            }

            // Also check for enclosed quote pattern "text"
            if (empty($result['quote']) && preg_match('/"([^"]+)"/', $fullText, $quoteMatches)) {
                $result['quote'] = trim($quoteMatches[1]);

                // Remove quote from text to get remaining content
                $contentWithoutQuote = preg_replace('/"[^"]+"\s*/', '', $fullText, 1);
                $contentWithoutQuote = trim($contentWithoutQuote);

                // Try to extract source from remaining text
                $contentLines = explode("\n", $contentWithoutQuote);
                if (!empty($contentLines)) {
                    $firstLine = trim($contentLines[0]);
                    if (!empty($firstLine) && strlen($firstLine) < 100 && preg_match('/^[^.]+[,.]\s*\d+|^[^.]+,\s*p\./', $firstLine)) {
                        $result['source'] = $firstLine;
                        $contentLines[0] = '';
                    }
                }
                $lines = $contentLines;
                $contentStartIndex = 0;
            }

            // Collect content from contentStartIndex onward
            $contentText = '';
            for ($i = $contentStartIndex; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (!empty($line)) {
                    if (!empty($contentText)) {
                        $contentText .= ' ';
                    }
                    $contentText .= $line;
                }
            }

            if (!empty($contentText)) {
                $result['content'] = [trim(preg_replace('/\s+/u', ' ', $contentText))];
            } elseif (empty($result['quote'])) {
                // No quote found, use entire content
                $result['content'] = [trim(preg_replace('/\s+/u', ' ', $fullText))];
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

        // Trim all string values
        $result = array_map(function ($value) {
            if (is_array($value)) {
                return array_map('trim', $value);
            }
            return trim($value);
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
