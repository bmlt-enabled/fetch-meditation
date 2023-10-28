<?php

namespace FetchMeditation;

class JFTSettings
{
    public readonly JFTLanguage $language;

    public function __construct(JFTLanguage $language)
    {
        $this->language = $language;
    }
}
