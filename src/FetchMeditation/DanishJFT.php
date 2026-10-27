<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class DanishJFT extends JFT
{
    // The feed labels each entry with a Danish month name rather than a number,
    // so we map the current month onto the same spelling to find today's entry.
    private const MONTHS = [
        1 => 'januar',
        2 => 'februar',
        3 => 'marts',
        4 => 'april',
        5 => 'maj',
        6 => 'juni',
        7 => 'juli',
        8 => 'august',
        9 => 'september',
        10 => 'oktober',
        11 => 'november',
        12 => 'december',
    ];

    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://raw.githubusercontent.com/Narcotics-Anonymous-Danmark/App/0a93008aa804492dae195b529ad3c4ca6a473d0c/src/assets/data/jft.json');
        $entries = json_decode($data, true);

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

        if (is_array($entries)) {
            // The feed carries the whole year, so we pick out today's entry locally,
            // honoring the configured time zone when deciding what "today" is.
            $timeZone = $this->settings->timeZone ?? date_default_timezone_get();
            $now = new \DateTime('now', new \DateTimeZone($timeZone));
            $day = (int) $now->format('j');
            $month = self::MONTHS[(int) $now->format('n')];

            $today = null;
            foreach ($entries as $entry) {
                if ((int) ($entry['day'] ?? 0) === $day && ($entry['month'] ?? '') === $month) {
                    $today = $entry;
                    break;
                }
            }

            if ($today !== null) {
                $result['date'] = trim(($today['day'] ?? '') . '. ' . ($today['month'] ?? ''));
                $result['title'] = $today['title'] ?? '';
                $result['quote'] = $today['quote'] ?? '';
                $result['source'] = $today['source'] ?? '';
                $result['thought'] = $today['jft'] ?? '';
                // The body arrives as a single string with newline-separated paragraphs.
                $content = preg_split('/\n/', trim($today['text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                $result['content'] = array_filter(array_map('trim', $content), fn($item) => $item !== '');
            }
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
