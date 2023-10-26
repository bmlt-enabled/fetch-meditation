<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class JFTTest extends TestCase
{
    public function testConstructorWithLanguageSetting()
    {
        $settings = new JFTSettings(['language' => 'es']);
        $jft = new JFT($settings);
        $this->assertEquals('es', $jft->getLanguage());
    }

    public function testConstructorWithoutLanguageSetting()
    {
        $jft = new JFT();
        // default language is 'en'
        $this->assertEquals('en', $jft->getLanguage());
    }

    public function testConstructorWithIvalidLanguageSetting()
    {
        $settings = new JFTSettings(['language' => 'de']);
        $jft = new JFT($settings);
        // default fallback language is 'en'
        $this->assertEquals('en', $jft->getLanguage());
    }

    public function testFetch()
    {
        $settings = new JFTSettings(['language' => 'en']);
        $jft = new JFT($settings);
        $entry = $jft->fetch();

        // Check if $entry is an instance of JFTEntry and contains expected data types
        $this->assertInstanceOf(JFTEntry::class, $entry);
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
