<?php

require __DIR__ . '/vendor/autoload.php';

use FetchMeditation\JFT;
use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\SPAD;
use FetchMeditation\SPADLanguage;
use FetchMeditation\SPADSettings;

function handler($event)
{
    $type = $event['type'] ?? 'jft';
    $language = $event['language'] ?? 'english';
    $timezone = $event['timezone'] ?? null;

    try {
        $langMap = [
            'english' => 'English',
            'french' => 'French',
            'german' => 'German',
            'italian' => 'Italian',
            'japanese' => 'Japanese',
            'portuguese' => 'Portuguese',
            'portuguese-pt' => 'PortuguesePT',
            'russian' => 'Russian',
            'spanish' => 'Spanish',
            'swedish' => 'Swedish'
        ];

        $langKey = strtolower($language);
        $enumCase = $langMap[$langKey] ?? 'English';

        if (strtolower($type) === 'spad') {
            $langEnum = constant(SPADLanguage::class . '::' . $enumCase);
            $settings = new SPADSettings($langEnum, $timezone);
            $fetcher = SPAD::getInstance($settings);
        } else {
            $langEnum = constant(JFTLanguage::class . '::' . $enumCase);
            $settings = new JFTSettings($langEnum, $timezone);
            $fetcher = JFT::getInstance($settings);
        }

        $data = $fetcher->fetch();

        return [
            'statusCode' => 200,
            'body' => json_encode([
                'success' => true,
                'data' => $data
            ])
        ];
    } catch (\Exception $e) {
        return [
            'statusCode' => 500,
            'body' => json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ])
        ];
    }
}
