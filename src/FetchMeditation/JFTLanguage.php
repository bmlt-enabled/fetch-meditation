<?php

namespace FetchMeditation;

enum JFTLanguage
{
    case English;
    case French;
    case German;
    case Italian;
    case Japanese;
    case Portuguese;
    case Russian;
    case Spanish;
    case Swedish;

    public function url(): string
    {
        return match ($this) {
            JFTLanguage::English => 'https://www.jftna.org/jft/',
            JFTLanguage::French => 'https://jpa.narcotiquesanonymes.org/',
            JFTLanguage::German => 'https://narcotics-anonymous.de/artikel/nur-fuer-heute/',
            JFTLanguage::Italian => 'https://na-italia.org/get-jft',
            JFTLanguage::Japanese => 'https://najapan.org/just_for_today/',
            JFTLanguage::Portuguese => 'https://www.na.org.br/meditacao/',
            JFTLanguage::Russian => 'https://na-russia.org/eg',
            JFTLanguage::Spanish => 'https://forozonalatino.org/wp-content/uploads/meditaciones/',
            JFTLanguage::Swedish => 'https://www.nasverige.org/dagens-text/',
        };
    }
}
