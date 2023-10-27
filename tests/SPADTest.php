<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class SPADTest extends TestCase
{
    public function testConstructorWithLanguageSetting()
    {
        $settings = new SPADSettings(SPADLanguage::English);
        $spad = SPAD::getInstance($settings);
        $this->assertEquals(SPADLanguage::English, $spad->getLanguage());
    }

    public function testFetch()
    {
        $settings = new SPADSettings(SPADLanguage::English);
        $spad = SPAD::getInstance($settings);
        $entry = $spad->fetch();

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
