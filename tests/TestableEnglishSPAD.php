<?php

namespace FetchMeditation;

/**
 * Test subclass that allows us to verify timeZone parameter is passed correctly
 */
class TestableEnglishSPAD extends EnglishSPAD
{
    public $capturedUrl;
    public $capturedParams;

    /**
     * Override to capture the URL and parameters
     * @param string $url The URL to fetch from
     * @param array $params The parameters to pass
     * @return string Mock data for testing
     */
    protected function fetchFromUrl(string $url, array $params = []): string
    {
        $this->capturedUrl = $url;
        $this->capturedParams = $params;

        // Return mock data
        return '<table><tr><td>April 22, 2025</td></tr><tr><td>Test Title</td></tr><tr><td>116</td></tr>' .
               '<tr><td>Test Quote</td></tr><tr><td>Test Source</td></tr><tr><td>Test Content</td></tr>' .
               '<tr><td>Test Thought</td></tr><tr><td>Test Copyright</td></tr></table>';
    }
}
