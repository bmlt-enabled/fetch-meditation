<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class SPADTest extends TestCase
{
    public function testConstructorWithLanguageSetting()
    {
        $settings = new SPADSettings(['language' => 'en']);
        $jft = new SPAD($settings);
        $this->assertEquals('en', $jft->getLanguage());
    }

    public function testConstructorWithoutLanguageSetting()
    {
        $jft = new SPAD();
        // default language is 'en'
        $this->assertEquals('en', $jft->getLanguage());
    }

    public function testConstructorWithIvalidLanguageSetting()
    {
        $settings = new SPADSettings(['language' => 'de']);
        $jft = new SPAD($settings);
        // default fallback language is 'en'
        $this->assertEquals('en', $jft->getLanguage());
    }

    public function testFetch()
    {
        $settings = new SPADSettings(['language' => 'en']);
        $jft = new SPAD($settings);
        $entry = $jft->fetch();

        // Check if $entry is an instance of JFTEntry and contains expected data types
        $this->assertInstanceOf(SPADEntry::class, $entry);
        $this->assertIsString($entry->date);
        $this->assertIsString($entry->title);
        $this->assertIsString($entry->page);
        $this->assertIsString($entry->quote);
        $this->assertIsString($entry->source);
        $this->assertIsArray($entry->content);
        $this->assertIsString($entry->thought);
        $this->assertIsString($entry->copyright);
    }
}
