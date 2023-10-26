<?php

namespace FetchMeditation;

class SPADSettings
{
    public string $language = "en";

    public function __construct(array $settings = [])
    {
        $validLanguages = ["en"];

        // Validate language
        if (isset($settings['language']) && in_array($settings['language'], $validLanguages)) {
            $this->language = $settings['language'];
        }

        return [
            'language' => $this->language,
        ];
    }
}
