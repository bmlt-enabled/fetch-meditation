<?php

namespace FetchMeditation;

use PHPUnit\Framework\TestCase;

class JFTEntryTest extends TestCase
{
    public function testJFTEntryProperties()
    {
        $entry = new JFTEntry(
            '2023-10-26',
            'Sample Title',
            'Sample Page',
            'Sample Quote',
            'Sample Source',
            ['Content Line 1', 'Content Line 2'],
            'Sample Thought',
            'Sample Copyright'
        );

        // Check that the properties exist and have the correct data types
        $this->assertIsString($entry->date);
        $this->assertIsString($entry->title);
        $this->assertIsString($entry->page);
        $this->assertIsString($entry->quote);
        $this->assertIsString($entry->source);
        $this->assertIsArray($entry->content);
        $this->assertIsString($entry->thought);
        $this->assertIsString($entry->copyright);
    }

    public function testJFTEntryStripTags()
    {
        $entry = new JFTEntry(
            '2023-10-26',
            'Sample <br>Title',
            'Sample Page',
            'Sample Quote',
            'Sample Source',
            ['Content Line 1', 'Content Line 2'],
            'Sample Thought',
            'Sample Copyright'
        );

        // Check that html tags have been stripped
        $this->assertEquals('Sample Title', $entry->stripTags()['title']);
    }

    public function testJFTEntryGetJson()
    {
        $entry = new JFTEntry(
            '2023-10-26',
            'Sample Title',
            'Sample Page',
            'Sample Quote',
            'Sample Source',
            ['Content Line 1', 'Content Line 2'],
            'Sample Thought',
            'Sample Copyright'
        );

        $responseData = $entry->getJson();
        $decodedResponse = json_decode($responseData);

        if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Response is not valid JSON');
        } else {
            $this->assertTrue(true);
            $this->assertArrayHasKey('date', (array)$decodedResponse);
            $this->assertArrayHasKey('title', (array)$decodedResponse);
            $this->assertArrayHasKey('page', (array)$decodedResponse);
            $this->assertArrayHasKey('quote', (array)$decodedResponse);
            $this->assertArrayHasKey('source', (array)$decodedResponse);
            $this->assertArrayHasKey('content', (array)$decodedResponse);
            $this->assertArrayHasKey('thought', (array)$decodedResponse);
            $this->assertArrayHasKey('copyright', (array)$decodedResponse);
        }
    }
}
