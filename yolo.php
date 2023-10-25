<?php

require_once __DIR__ . '/src/FetchMeditation/JFT.php';
require_once __DIR__ . '/src/FetchMeditation/JFTSettings.php';

use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

$settings = new JFTSettings(['language' => "en"]);
$jft = new JFT($settings);
$entry = $jft->fetch();
print_r($entry->copyright);
//echo json_encode($entry);

