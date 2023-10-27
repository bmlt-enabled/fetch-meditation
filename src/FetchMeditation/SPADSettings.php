<?php

namespace FetchMeditation;

class SPADSettings
{
    public readonly SPADLanguage $language;

    public function __construct(SPADLanguage $language)
    {
        $this->language = $language;
    }
}
