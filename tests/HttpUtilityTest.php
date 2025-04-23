<?php

namespace FetchMeditation\Utilities;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HttpUtilityTest extends TestCase
{
    public function testAppendQueryParams()
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass(HttpUtility::class);
        $method = $reflection->getMethod('appendQueryParams');

        // Test appending to URL without existing query parameters
        $url = 'https://jft.na.org';
        $params = ['timeZone' => 'America/New_York'];
        $result = $method->invoke(null, $url, $params);
        $this->assertEquals('https://jft.na.org?timeZone=America%2FNew_York', $result);

        // Test appending to URL with existing query parameters
        $url = 'https://jft.na.org?param=value';
        $result = $method->invoke(null, $url, $params);
        $this->assertEquals('https://jft.na.org?param=value&timeZone=America%2FNew_York', $result);
    }
}
