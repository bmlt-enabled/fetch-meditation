<?php

namespace FetchMeditation;

require_once __DIR__ . '/../../vendor/autoload.php';

abstract class SPAD
{
    protected SPADSettings $settings;

    public function __construct(SPADSettings $settings)
    {
        $this->settings = $settings;
    }

    abstract protected function fetch();

    abstract protected function getLanguage();

    public static function getInstance(SPADSettings $settings): SPAD
    {
        return match ($settings->language) {
            SPADLanguage::English => new EnglishSPAD($settings),
        };
    }
}
