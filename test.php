<?php

require_once __DIR__ . '/src/FetchMeditation/JFT.php';
require_once __DIR__ . '/src/FetchMeditation/JFTSettings.php';

use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

echo "JFT\n\n";

$settings = new JFTSettings(['language' => "en"]);
$jft = new JFT($settings);
$entry = $jft->fetch();
print_r($entry->quote);

echo "\n\nSPAD\n\n";

use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;

$settings = new SPADSettings();
$spad = new SPAD($settings);
$entry = $spad->fetch();
print_r($entry->quote);
