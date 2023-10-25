<?php

namespace Tests;

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use FetchMeditation\Config;

class ConfigTest extends TestCase
{
    public function testDefaultValues()
    {
        $config = new Config();
        $configGet = $config->get();

        $this->assertEquals("jft", $configGet['book']);
        $this->assertEquals("en", $configGet['language']);
        $this->assertEquals("html", $configGet['outputType']);
    }

    public function testValidOptions()
    {
        // TODO: These aren't complete
        $config = new Config();

        // Test valid book options
        $config->init(['book' => 'spad']);

        // Test valid language options
        $config->init(['language' => 'en']);

        // Test valid output type options
        $config->init(['outputType' => 'json']);

        $configGet = $config->get();
        $this->assertEquals("spad", $configGet['book']);
        $this->assertEquals("en", $configGet['language']);
        $this->assertEquals("json", $configGet['outputType']);
    }

    public function testInvalidOptions()
    {
        $config = new Config();

        // Test invalid book option
        $config->init(['book' => 'invalid']);

        // Test invalid language option
        $config->init(['language' => 'fr']);

        // Test invalid output type option
        $config->init(['outputType' => 'pdf']);

        $configGet = $config->get();
        $this->assertEquals("jft", $configGet['book']);
        $this->assertEquals("en", $configGet['language']);
        $this->assertEquals("html", $configGet['outputType']);
    }
}
