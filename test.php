<?php

require_once __DIR__ . '/vendor/autoload.php';

use FetchMeditation\SPADLanguage;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;

use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

foreach (JFTLanguage::cases() as $language) {
    echo $language->name . " (" . $language->value . ")\n";
}

// $settings = new JFTSettings(JFTLanguage::Spanish);
// $jft = JFT::getInstance($settings);
// $entry = $jft->fetch();
// if (is_string($entry)) {
//     echo "Error: {$entry}";
// } else {
//     echo $entry->toJson();
// }

// $settings = new JFTSettings(JFTLanguage::English, 'Australia/Sydney');
// $jft = JFT::getInstance($settings);
// $entry = $jft->fetch();
// if (is_string($entry)) {
//     echo "Error: {$entry}";
// } else {
//     echo $entry->toJson();
// }

//foreach (JFTLanguage::cases() as $shape) {
//    if ($shape->name == 'French') {
//        // French server is really slow
//        continue;
//    }
//    echo "\n\n-=-=-=-=-=-=-=-= JFT - $shape->name -=-=-=-=-=-=-=-=\n\n";
//    $settings = new JFTSettings($shape);
//    $jft = JFT::getInstance($settings);
//    $entry = $jft->fetch();
//    print_r($entry->quote);
//    echo "-- " . $jft->getLanguage()->name;
//}
//
//foreach (SPADLanguage::cases() as $shape) {
//    echo "\n\n-=-=-=-=-=-=-=-= SPAD - $shape->name -=-=-=-=-=-=-=-=\n\n";
//    $settings = new SPADSettings($shape);
//    $spad = SPAD::getInstance($settings);
//    $entry = $spad->fetch();
//    print_r($entry->quote);
//    echo "-- " . $spad->getLanguage()->name;
//}
//$entries = [];
//foreach (JFTLanguage::cases() as $shape) {
//    $settings = new JFTSettings($shape);
//    $jft = JFT::getInstance($settings);
//    $entry = $jft->fetch();
//    $entries[$shape->name] = $entry;
//}
//echo json_encode($entries);
