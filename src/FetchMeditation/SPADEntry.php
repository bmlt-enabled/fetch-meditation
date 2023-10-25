<?php

namespace FetchMeditation;

class SPADEntry {
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
    )
    {
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
        return json_encode($this);
    }

    public function getHtml() {
        $paragraphs = "";
        foreach ($this->content as $c) {
            $paragraph = str_replace("--", "&mdash;", $c);
            $paragraphs .= "<p>{$paragraph}</p>";
        }

        return <<<HTML
<table align="center">
    <tr>
        <td align="left"><h2>{$this->date}</h2></td>
    </tr>
    <tr>
        <td align="center"><h1>{$this->title}</h1></td>
    </tr>
    <tr>
        <td align="center">{$this->page}<br><br></td>
    </tr>
    <tr>
        <td align="left">{$this->quote}<br><br></td>
    </tr>
    <tr>
        <td align="center">{$this->source}<br><br></td>
    </tr>
    <tr>
        <td align="left">{$paragraphs}</td>
    </tr>
  <tr>
        <td align="center">&mdash; &nbsp;  &nbsp; &mdash; &nbsp;  &nbsp; &mdash; &nbsp;  &nbsp; &mdash; &nbsp;  &nbsp; &mdash;<br><br></td>
    </tr>    <tr>
        <td align="left">{$this->thought}<br><br></td>
    </tr>

    <tr>
        <td align="center">{$this->copyright}</td>
    </tr>
</table>
HTML;
    }

    public function getCss() {
        $paragraphs = "";
        $count = 1;
        foreach ($this->content as $c) {
            $paragraphs .= "<p id=\"spad-content-$count\" class=\"spad-rendered-element\">$c</p>";
            $count++;
        }

        return <<<CSS
<div id="spad-container" class="spad-rendered-element">
    <div id="spad-date" class="spad-rendered-element">{$this->date}</div>
    <div id="spad-title" class="spad-rendered-element">{$this->title}</div>
    <div id="spad-page" class="spad-rendered-element">{$this->page}</div>
    <div id="spad-quote" class="spad-rendered-element">{$this->quote}</div>
    <div id="spad-quote-source" class="spad-rendered-element">{$this->source}</div>
    <div id="spad-content" class="spad-rendered-element">
    {$paragraphs}
    </div>
    <div id="spad-thought" class="spad-rendered-element">{$this->thought}</div>
    <div id="spad-copyright" class="spad-rendered-element">{$this->copyright}</div>
</div>
CSS;
    }
}
