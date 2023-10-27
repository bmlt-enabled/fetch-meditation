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

    public static function getInstance(SPADSettings $settings)
    {
        return match ($settings->language) {
            SPADLanguage::English => new EnglishSPAD($settings),
        };
    }
}
