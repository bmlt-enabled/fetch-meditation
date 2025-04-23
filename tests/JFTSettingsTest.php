<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class JFTSettingsTest extends TestCase
{
    public function testConstructorWithoutTimeZone()
    {
        $settings = new JFTSettings(JFTLanguage::English);
        $jft = JFT::getInstance($settings);
        $this->assertEquals(JFTLanguage::English, $jft->getLanguage());
        $this->assertNull($settings->timeZone);
    }

    public function testConstructorWithTimeZone()
    {
        $timeZone = 'America/New_York';
        $settings = new JFTSettings(JFTLanguage::English, $timeZone);
        $this->assertEquals(JFTLanguage::English, $settings->language);
        $this->assertEquals($timeZone, $settings->timeZone);
    }
}
