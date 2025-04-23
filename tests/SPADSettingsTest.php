<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class SPADSettingsTest extends TestCase
{
    public function testConstructorWithoutTimeZone()
    {
        $settings = new SPADSettings(SPADLanguage::English);
        $spad = SPAD::getInstance($settings);
        $this->assertEquals(SPADLanguage::English, $spad->getLanguage());
        $this->assertNull($settings->timeZone);
    }

    public function testConstructorWithTimeZone()
    {
        $timeZone = 'America/New_York';
        $settings = new SPADSettings(SPADLanguage::English, $timeZone);
        $this->assertEquals(SPADLanguage::English, $settings->language);
        $this->assertEquals($timeZone, $settings->timeZone);
    }
}
