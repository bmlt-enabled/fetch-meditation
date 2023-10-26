<?php

namespace FetchMeditation;

require_once __DIR__ . '/../../vendor/autoload.php';

use FetchMeditation\Languages\JFT\EnglishLanguage;
use FetchMeditation\Languages\JFT\FrenchLanguage;
use FetchMeditation\Languages\JFT\ItalianLanguage;
use FetchMeditation\Languages\JFT\JapaneseLanguage;
use FetchMeditation\Languages\JFT\PortugueseLanguage;
use FetchMeditation\Languages\JFT\RussianLanguage;
use FetchMeditation\Languages\JFT\SpanishLanguage;
use FetchMeditation\Languages\JFT\SwedishLanguage;

class JFT
{
    private string $language;
    private $languageHandler;

    public function __construct($settings = null)
    {
        $this->language = $settings->language ?? 'en';
        $this->languageHandler = match ($this->language) {
            'en' => new EnglishLanguage(),
            'es' => new SpanishLanguage(),
            'fr' => new FrenchLanguage(),
            'it' => new ItalianLanguage(),
            'ja' => new JapaneseLanguage(),
            'pt' => new PortugueseLanguage(),
            'ru' => new RussianLanguage(),
            'sv' => new SwedishLanguage(),
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
