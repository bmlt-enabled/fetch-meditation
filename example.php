<?php

require_once __DIR__ . '/vendor/autoload.php';

use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

$settings = new JFTSettings(JFTLanguage::English);
$jft = JFT::getInstance($settings);
$data = $jft->fetch();

$parts = [
    $data->date,
    $data->title,
    $data->page,
    $data->quote,
    $data->source
];

foreach ($data->content as $paragraph) {
    $parts[] = strip_tags(html_entity_decode($paragraph));
}

$parts[] = $data->thought;
$parts[] = $data->copyright;
$parts = array_filter($parts, fn($part) => !empty(trim($part)));
echo implode("\n\n", $parts);
