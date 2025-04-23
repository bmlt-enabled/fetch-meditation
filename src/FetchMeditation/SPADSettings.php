<?php

namespace FetchMeditation;

class SPADSettings
{
    public readonly SPADLanguage $language;
    public readonly ?string $timeZone;

    public function __construct(SPADLanguage $language, ?string $timeZone = null)
    {
        $this->language = $language;
        $this->timeZone = $timeZone;
    }
}
