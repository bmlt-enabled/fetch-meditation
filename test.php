<?php

require_once __DIR__ . '/vendor/autoload.php';

use FetchMeditation\SPADLanguage;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;

use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

//$settings = new JFTSettings(JFTLanguage::English);
//$jft = JFT::getInstance($settings);
//$entry = $jft->fetch();
//echo $entry->quote;

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

foreach (JFTLanguage::cases() as $shape) {
    if ($shape->name == 'French') {
        // French server is really slow
        continue;
    }
    $settings = new JFTSettings($shape);
    $jft = JFT::getInstance($settings);
    $entry = $jft->fetch();
    print_r($entry->toJson());
}
