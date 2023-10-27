<?php

namespace FetchMeditation;

enum SPADLanguage
{
    case English;
}

class SPADSettings
{
    public readonly SPADLanguage $language;

    public function __construct(SPADLanguage $language)
    {
        $this->language = $language;
    }
}
