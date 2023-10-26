<?php

namespace FetchMeditation;

require_once __DIR__ . '/../../vendor/autoload.php';

use FetchMeditation\Languages\SPAD\EnglishLanguage;

class SPAD
{
    private string $language;
    private $languageHandler;

    public function __construct($settings = null)
    {
        $this->language = $settings->language ?? 'en';
        $this->languageHandler = match ($this->language) {
            'en' => new EnglishLanguage(),
            default => new EnglishLanguage(),
        };
    }

    public function fetch()
    {
        return $this->languageHandler->fetch();
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
