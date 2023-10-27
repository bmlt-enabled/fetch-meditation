<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class SPADSettingsTest extends TestCase
{
    public function testConstructor()
    {
        $settings = new SPADSettings(SPADLanguage::English);
        $spad = SPAD::getInstance($settings);
        $this->assertEquals(SPADLanguage::English, $spad->getLanguage());
    }
}
