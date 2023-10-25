<?php

namespace FetchMeditation;

class Config
{
    private $book = "jft";
    private $language = "en";
    private $outputType = "html";

    public function init($options = [])
    {
        $validBooks = ["jft", "spad"];
        $validLanguagesForJft = ["en", "es", "it"];
        $validLanguagesForSpad = ["en", "fr"];
        $validOutputTypes = ["json", "html", "block"];

        // Validate book option
        if (isset($options['book']) && in_array($options['book'], $validBooks)) {
            $this->book = $options['book'];
        }

        // Validate language option based on selected book
        if ($this->book === "jft" && isset($options['language']) && in_array($options['language'], $validLanguagesForJft)) {
            $this->language = $options['language'];
        } elseif ($this->book === "spad" && isset($options['language']) && in_array($options['language'], $validLanguagesForSpad)) {
            $this->language = $options['language'];
        }

        // Validate output type option based on language and book
        if (isset($options['outputType'])) {
            if (
                in_array($options['outputType'], $validOutputTypes) &&
                (
                    ($this->book === "jft" && $this->language === "en") ||
                    ($this->book === "spad")
                )
            ) {
                $this->outputType = $options['outputType'];
            }
        }
        return [
            'book' => $this->book,
            'language' => $this->language,
            'outputType' => $this->outputType,
        ];
    }

    public function get()
    {
        return [
            'book' => $this->book,
            'language' => $this->language,
            'outputType' => $this->outputType,
        ];
    }
}
