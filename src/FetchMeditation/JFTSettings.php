<?php

namespace FetchMeditation;

class JFTSettings
{
    public string $language = "en";

    public function __construct(array $settings = [])
    {
        $validLanguages = ["en", "es", "pt", "it", "ru"];

        // Validate language
        if (isset($settings['language']) && in_array($settings['language'], $validLanguages)) {
            $this->language = $settings['language'];
        }

        return [
            'language' => $this->language,
        ];
    }
}
