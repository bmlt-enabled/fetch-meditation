<?php

require_once __DIR__ . '/src/FetchMeditation/JFT.php';
require_once __DIR__ . '/src/FetchMeditation/JFTSettings.php';

use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

echo "\n\n-=-=-=-=-=-=-=-= SPAD - EN -=-=-=-=-=-=-=-=\n\n";
$settings = new SPADSettings();
$spad = new SPAD($settings);
$entry = $spad->fetch();
print_r($entry->quote);

$langs = ["en", "es", "it", "ja", "pt", "ru", "sv"];
// French server is slow so like to comment out `"fr"` for testing a lot
foreach ($langs as $lang) {
    echo "\n\n-=-=-=-=-=-=-=-= JFT - " . strtoupper($lang) . " -=-=-=-=-=-=-=-=\n\n";
    $settings = new JFTSettings(['language' => $lang]);
    $jft = new JFT($settings);
    $entry = $jft->fetch();
    print_r($entry->quote);
}
