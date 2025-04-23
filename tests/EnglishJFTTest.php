<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;
use FetchMeditation\TestableEnglishJFT;

class EnglishJFTTest extends TestCase
{
    public function testFetchWithTimeZone()
    {
        // Create settings with a timeZone
        $timeZone = 'America/New_York';
        $settings = new JFTSettings(JFTLanguage::English, $timeZone);

        // Use our testable subclass
        $jft = new TestableEnglishJFT($settings);
        $result = $jft->fetch();

        // Verify the timeZone parameter was passed correctly
        $this->assertEquals('https://jft.na.org', $jft->capturedUrl);
        $this->assertIsArray($jft->capturedParams);
        $this->assertArrayHasKey('timeZone', $jft->capturedParams);
        $this->assertEquals($timeZone, $jft->capturedParams['timeZone']);

        // Verify the result is a JFTEntry
        $this->assertInstanceOf(JFTEntry::class, $result);
    }
}
