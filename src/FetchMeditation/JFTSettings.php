<?php

namespace FetchMeditation;

class JFTSettings
{
    public readonly JFTLanguage $language;
    public readonly ?string $timeZone;

    public function __construct(JFTLanguage $language, ?string $timeZone = null)
    {
        $this->language = $language;
        $this->timeZone = $timeZone;
    }
}
