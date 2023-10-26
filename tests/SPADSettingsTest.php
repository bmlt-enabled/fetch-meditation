<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class SPADSettingsTest extends TestCase
{
    public function testValidLanguagesInitialization()
    {
        $settings = new SPADSettings();
        $this->assertEquals(
            ["en"],
            $settings->validLanguages
        );
    }

    public function testDefaultLanguage()
    {
        $settings = new SPADSettings();
        $this->assertEquals('en', $settings->language);
    }

    public function testInvalidLanguage()
    {
        $settings = new SPADSettings(['language' => 'de']);
        $this->assertEquals('en', $settings->language);
    }

    public function testValidLanguageEn()
    {
        $settings = new SPADSettings(['language' => 'en']);
        $this->assertEquals('en', $settings->language);
    }
}
