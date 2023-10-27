<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class SpanishJFT extends JFT
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
        $data = HttpUtility::httpGet('https://forozonalatino.org/wp-content/uploads/meditaciones/' . static::getTimezoneDate('spanish', 'm/d') . '.html');
        $doc = new \DOMDocument();
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
                $paragraphs[] = $paragraph;
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

        $content = [];
        $fetchElements = $doc->getElementsByTagName('p');
        foreach ($fetchElements as $element) {
            $class = $element->getAttribute('class');
            switch ($class) {
                case 'fecha-sxh':
                    $content['date'] = $element->nodeValue;
                    break;
                case 'titulo-sxh':
                    $content['title'] = $element->nodeValue;
                    break;
                case 'descripcion-sxh':
                    $content['quote'] = $element->nodeValue;
                    break;
                case 'numero-pagina-sxh':
                    $content['source'] = $element->nodeValue;
                    break;
                case 'soloxhoycontainer':
                    $content['thought'] = $element->nodeValue;
                    break;
            }
        }

        $content['content'] = $paragraphs;
        $content['page'] = '';
        $content['copyright'] = 'Servicio del Foro Zonal Latinoamericano, Copyright 2017 NA World Services, Inc. Todos los Derechos Reservados.';
        $content['thought']  = 'Sólo por Hoy: ' . trim($extractedThought);

        return $content;
    }

    private static function getTimezoneDate(string $language, $format = 'md'): string
    {
        $timezoneMap = [
            'german' => 'Europe/Berlin',
            'swedish' => 'Europe/Stockholm',
            'danish' => 'Europe/Copenhagen',
            'italian' => 'Europe/Rome',
            'spanish' => 'America/Mexico_City',
        ];

        $timezone = array_key_exists($language, $timezoneMap) ? new \DateTimeZone($timezoneMap[$language]) : new \DateTimeZone('America/New_York');
        $date = new \DateTime('now', $timezone);
        return $date->format($format);
    }
}
