<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class SpanishJFT extends JFT
{
    public function getLanguage(): JFTLanguage
    {
        return $this->settings->language;
    }

    public function fetch(): JFTEntry
    {
        $timezone = new \DateTimeZone('America/Mexico_City');
        $date = new \DateTime('now', $timezone);
        $data = HttpUtility::httpGet('https://forozonalatino.org/wp-content/uploads/meditaciones/' . $date->format('m/d') . '.html');
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="ISO-8859-1">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($doc);

        // Get content
        $paragraphs = [];
        for ($i = 1; $i <= 3; $i++) {
            $query = "//p[preceding-sibling::comment()[contains(., 'PARRAFO $i')]]";
            $paragraphNodes = $xpath->query($query);

            if ($paragraphNodes->length > 0) {
                $paragraph = trim($paragraphNodes->item(0)->textContent);
                $paragraphs[] = str_replace("\n", "", $paragraph);
                ;
            }
        }

        // Get Thought
        $extractedThought = '';
        $startComment = $xpath->query('//comment()[contains(., "SOLO X HOY insertar AQUI sin el Solo por Hoy")]')->item(0);
        $endComment = $xpath->query('//comment()[contains(., "FIN SOLO X HOY")]')->item(0);

        if ($startComment && $endComment) {
            $startNode = $startComment->nextSibling;
            while ($startNode && $startNode !== $endComment) {
                $extractedThought .= $doc->saveHTML($startNode);
                $startNode = $startNode->nextSibling;
            }
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

        $result['content'] = $paragraphs;
        $result['page'] = '';
        $result['copyright'] = 'Servicio del Foro Zonal Latinoamericano, Copyright 2017 NA World Services, Inc. Todos los Derechos Reservados.';
        $result['thought']  = 'Sólo por Hoy: ' . trim($extractedThought);

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
