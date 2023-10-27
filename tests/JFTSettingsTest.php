<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class JFTSettingsTest extends TestCase
{
    public function testConstructor()
    {
        $settings = new JFTSettings(JFTLanguage::English);
        $jft = JFT::getInstance($settings);
        $this->assertEquals(JFTLanguage::English, $jft->getLanguage());
    }
}
