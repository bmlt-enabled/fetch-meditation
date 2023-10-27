<?php

require_once __DIR__ . '/src/FetchMeditation/JFT.php';
require_once __DIR__ . '/src/FetchMeditation/JFTSettings.php';

use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

use FetchMeditation\SPADLanguage;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;


foreach (JFTLanguage::cases() as $shape) {
    if ($shape->name == 'French') {
        continue;
    }
    echo "\n\n-=-=-=-=-=-=-=-= JFT - $shape->name -=-=-=-=-=-=-=-=\n\n";
    $settings = new JFTSettings($shape);
    $jft = JFT::getInstance($settings);
    $entry = $jft->fetch();
    print_r($entry->quote);
    echo "-- " . $jft->getLanguage()->name;
}

foreach (SPADLanguage::cases() as $shape) {
    echo "\n\n-=-=-=-=-=-=-=-= SPAD - $shape->name -=-=-=-=-=-=-=-=\n\n";
    $settings = new SPADSettings($shape);
    $spad = SPAD::getInstance($settings);
    $entry = $spad->fetch();
    print_r($entry->quote);
    echo "-- " . $spad->getLanguage()->name;
}
