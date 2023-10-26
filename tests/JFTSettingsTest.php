<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class JFTSettingsTest extends TestCase
{
    public function testValidLanguagesInitialization()
    {
        $settings = new JFTSettings();
        $this->assertEquals(
            ["en", "es", "fr", "it", "ja", "pt", "ru", "sv"],
            $settings->validLanguages
        );
    }

    public function testDefaultLanguage()
    {
        $settings = new JFTSettings();
        $this->assertEquals('en', $settings->language);
    }

    public function testInvalidLanguage()
    {
        $settings = new JFTSettings(['language' => 'de']);
        $this->assertEquals('en', $settings->language);
    }

    public function testValidLanguageEn()
    {
        $settings = new JFTSettings(['language' => 'en']);
        $this->assertEquals('en', $settings->language);
    }

    public function testValidLanguageEs()
    {
        $settings = new JFTSettings(['language' => 'es']);
        $this->assertEquals('es', $settings->language);
    }

    public function testValidLanguageFr()
    {
        $settings = new JFTSettings(['language' => 'fr']);
        $this->assertEquals('fr', $settings->language);
    }

    public function testValidLanguageIt()
    {
        $settings = new JFTSettings(['language' => 'it']);
        $this->assertEquals('it', $settings->language);
    }

    public function testValidLanguageJa()
    {
        $settings = new JFTSettings(['language' => 'ja']);
        $this->assertEquals('ja', $settings->language);
    }

    public function testValidLanguagePt()
    {
        $settings = new JFTSettings(['language' => 'pt']);
        $this->assertEquals('pt', $settings->language);
    }

    public function testValidLanguageRu()
    {
        $settings = new JFTSettings(['language' => 'ru']);
        $this->assertEquals('ru', $settings->language);
    }

    public function testValidLanguageSv()
    {
        $settings = new JFTSettings(['language' => 'sv']);
        $this->assertEquals('sv', $settings->language);
    }
}
