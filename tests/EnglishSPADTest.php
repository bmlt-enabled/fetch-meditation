<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;
use FetchMeditation\TestableEnglishSPAD;

class EnglishSPADTest extends TestCase
{
    public function testFetchWithTimeZone()
    {
        // Create settings with a timeZone
        $timeZone = 'America/New_York';
        $settings = new SPADSettings(SPADLanguage::English, $timeZone);

        // Use our testable subclass
        $spad = new TestableEnglishSPAD($settings);
        $result = $spad->fetch();

        // Verify the timeZone parameter was passed correctly
        $this->assertEquals('https://spad.na.org', $spad->capturedUrl);
        $this->assertIsArray($spad->capturedParams);
        $this->assertArrayHasKey('timeZone', $spad->capturedParams);
        $this->assertEquals($timeZone, $spad->capturedParams['timeZone']);

        // Verify the result is a SPADEntry
        $this->assertInstanceOf(SPADEntry::class, $result);
    }

    /**
     * Helper method to set a static property value on a class
     *
     * @param string $class The class name
     * @param string $property The property name
     * @param mixed $value The value to set
     */
    private function setStaticPropertyValue($class, $property, $value)
    {
        $reflection = new \ReflectionClass($class);
        if ($reflection->hasProperty($property)) {
            $reflectionProperty = $reflection->getProperty($property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue(null, $value);
        }
    }
}
