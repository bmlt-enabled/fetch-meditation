<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class JFTTest extends TestCase
{
    public function testConstructorWithLanguageSetting()
    {
        $settings = new JFTSettings(JFTLanguage::Spanish);
        $jft = JFT::getInstance($settings);
        $this->assertEquals(JFTLanguage::Spanish, $jft->getLanguage());
    }

    public function testFetch()
    {
        $settings = new JFTSettings(JFTLanguage::English);
        $jft = JFT::getInstance($settings);
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
