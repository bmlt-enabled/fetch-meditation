<?php

namespace FetchMeditation;

enum SPADLanguage
{
    case English;

    public function url(): string
    {
        return match ($this) {
            SPADLanguage::English => 'https://spadna.org',
        };
    }
}
