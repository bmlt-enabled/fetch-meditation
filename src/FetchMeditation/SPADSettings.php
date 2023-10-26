<?php

namespace FetchMeditation;

class SPADSettings
{
    public string $outputType = "html";
    public function __construct(array $settings = [])
    {
        $validOutputTypes = ["json", "html", "block"];

        // Validate output type option based on language
        if (isset($settings['outputType'])) {
            if (in_array($settings['outputType'], $validOutputTypes)) {
                $this->outputType = $settings['outputType'];
            }
        }
        return [
            'outputType' => $this->outputType,
        ];
    }
}
