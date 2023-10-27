<?php

require_once __DIR__ . '/src/FetchMeditation/JFT.php';
require_once __DIR__ . '/src/FetchMeditation/JFTSettings.php';

use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

use FetchMeditation\SPADLanguage;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;

//echo "\n\n-=-=-=-=-=-=-=-= SPAD - EN -=-=-=-=-=-=-=-=\n\n";
//
//$settings = new SPADSettings(SPADLanguage::English);
//$spad = SPAD::getInstance($settings);
//$entry = $spad->fetch();
//print_r($entry->quote);
//print_r($spad->getLanguage());

echo "\n\n-=-=-=-=-=-=-=-= JFT - EN -=-=-=-=-=-=-=-=\n\n";

$settings = new JFTSettings(JFTLanguage::English);
$jft = JFT::getInstance($settings);
$entry = $jft->fetch();
print_r($entry);
print_r($jft->getLanguage());


//case English;
//case French;
//case Italian;
//case Japanese;
//case Portuguese;
//case Russian;
//case Spanish;
//case Swedish;
