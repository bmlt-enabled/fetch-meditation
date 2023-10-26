<?php

namespace FetchMeditation;

class SPADSettings
{
    public string $language = "en";
    public array $validLanguages;

    public function __construct(array $settings = [])
    {
        $this->validLanguages = ["en"];

        // Validate language
        if (isset($settings['language']) && in_array($settings['language'], $this->validLanguages)) {
            $this->language = $settings['language'];
        }
    }
}
