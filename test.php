<?php

require_once __DIR__ . '/src/FetchMeditation/Config.php';
require_once __DIR__ . '/src/FetchMeditation/Reading.php';

use FetchMeditation\Config;
use FetchMeditation\Reading;
use FetchMeditation\HttpClient;

$config = new Config();
$settings = $config->init(['book' => "spad"]);

$reading = new Reading();
print_r($reading->get($settings));
