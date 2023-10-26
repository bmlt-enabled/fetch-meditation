<?php

namespace FetchMeditation;

class JFTSettings
{
    public string $language = "en";
    public array $validLanguages;

    public function __construct(array $settings = [])
    {
        $this->validLanguages = ["en", "es", "fr", "it", "pt", "ru", "sv"];

        // Validate language
        if (isset($settings['language']) && in_array($settings['language'], $this->validLanguages)) {
            $this->language = $settings['language'];
        }
    }
}
