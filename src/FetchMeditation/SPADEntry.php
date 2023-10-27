<?php

namespace FetchMeditation;

class SPADEntry
{
    public string $date;
    public string $title;
    public string $page;
    public string $quote;
    public string $source;
    public array $content;
    public string $thought;
    public string $copyright;

    public function __construct(
        string $date,
        string $title,
        string $page,
        string $quote,
        string $source,
        array $content,
        string $thought,
        string $copyright,
    ) {
        $this->date = $date;
        $this->title = $title;
        $this->page = $page;
        $this->quote = $quote;
        $this->source = $source;
        $this->content = $content;
        $this->thought = $thought;
        $this->copyright = $copyright;
    }

    public function getJson(): string
    {
        return json_encode((array)$this);
    }

    public function withoutTags(): array
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return array_map('strip_tags', $item);
            } else {
                return strip_tags((string) $item);
            }
        }, (array)$this);
    }
}
