<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class SpanishJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $timezone = new \DateTimeZone('America/Mexico_City');
        $date = new \DateTime('now', $timezone);
        $data = HttpUtility::httpGet('https://fzla.org/wp-content/uploads/meditaciones/' . $date->format('m/d') . '.html');
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="ISO-8859-1">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        $paragraphs = [];
        $contentDiv = $xpath->query('//div[@id="content"]')->item(0);

        if ($contentDiv) {
            // select all paragraphs within the content div except those with separa-sxh class
            $paragraphNodes = $xpath->query('.//p[not(contains(@class, "separa-sxh"))]', $contentDiv);

            foreach ($paragraphNodes as $node) {
                // skip the "Sólo por hoy" paragraph and empty paragraphs
                if (
                    !preg_match('/^Sólo por hoy/i', trim($node->textContent)) &&
                    !empty(trim($node->textContent)) &&
                    !$node->getElementsByTagName('img')->length
                ) {
                    $paragraphs[] = str_replace("\n", "", trim($node->textContent));
                }
            }
        }

        $extractedThought = '';
        $thoughtNode = $xpath->query('//div[@id="content"]//p[contains(., "Sólo por hoy")]')->item(0);

        if ($thoughtNode) {
            $extractedThought = trim($thoughtNode->textContent);
        }

        $result = [];
        $fetchElements = $doc->getElementsByTagName('p');
        foreach ($fetchElements as $element) {
            $class = $element->getAttribute('class');
            switch ($class) {
                case 'fecha-sxh':
                    $result['date'] = $element->nodeValue;
                    break;
                case 'titulo-sxh':
                    $result['title'] = $element->nodeValue;
                    break;
                case 'descripcion-sxh':
                    $result['quote'] = str_replace("\n", "", $element->nodeValue);
                    break;
                case 'numero-pagina-sxh':
                    $result['source'] = $element->nodeValue;
                    break;
                case 'soloxhoycontainer':
                    $result['thought'] = $element->nodeValue;
                    break;
            }
        }

        $result['content'] = array_values(array_filter($paragraphs));
        $result['page'] = '';
        $result['copyright'] = 'Servicio del Foro Zonal Latinoamericano, Copyright 2017 NA World Services, Inc. Todos los Derechos Reservados.';

        if (!empty($extractedThought)) {
            $result['thought'] = str_replace("\n", "", $extractedThought);
        } else if (empty($result['thought'])) {
            $result['thought'] = 'Sólo por Hoy: ';
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
