<?php

namespace FetchMeditation;

readonly class SPADSettings
{
    public SPADLanguage $language;

    public function __construct(SPADLanguage $language)
    {
        $this->language = $language;
    }
}
