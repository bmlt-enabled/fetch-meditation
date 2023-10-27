<?php

namespace FetchMeditation;

readonly class JFTSettings
{
    public JFTLanguage $language;

    public function __construct(JFTLanguage $language)
    {
        $this->language = $language;
    }
}
