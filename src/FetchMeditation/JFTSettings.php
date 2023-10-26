<?php

namespace FetchMeditation;

class JFTSettings
{
    public string $language = "en";
    public string $outputType = "html";
    public function __construct(array $settings = [])
    {
        $validLanguages = ["en", "es", "pt"];
        $validOutputTypes = ["json", "html", "block"];

        // Validate language
        if (isset($settings['language']) && in_array($settings['language'], $validLanguages)) {
            $this->language = $settings['language'];
        }

        // Validate output type option based on language
        if (isset($settings['outputType'])) {
            if (in_array($settings['outputType'], $validOutputTypes) && ($this->language === "en")) {
                $this->outputType = $settings['outputType'];
            }
        }
        return [
            'language' => $this->language,
            'outputType' => $this->outputType,
        ];
    }
}
