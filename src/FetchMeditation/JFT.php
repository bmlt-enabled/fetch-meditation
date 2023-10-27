<?php

namespace FetchMeditation;

require_once __DIR__ . '/../../vendor/autoload.php';

abstract class JFT
{
    protected JFTSettings $settings;

    public function __construct(JFTSettings $settings)
    {
        $this->settings = $settings;
    }

    abstract protected function fetch();

    public static function getInstance(JFTSettings $settings)
    {
        return match ($settings->language) {
            JFTLanguage::English => new EnglishJFT($settings),
            JFTLanguage::French => new FrenchJFT($settings),
            JFTLanguage::Italian => new ItalianJFT($settings),
            JFTLanguage::Japanese => new JapaneseJFT($settings),
            JFTLanguage::Portuguese => new PortugueseJFT($settings),
            JFTLanguage::Russian => new RussianJFT($settings),
            JFTLanguage::Spanish => new SpanishJFT($settings),
            JFTLanguage::Swedish => new SwedishJFT($settings),
        };
    }
}
