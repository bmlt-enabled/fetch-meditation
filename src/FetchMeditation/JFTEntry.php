<?php

namespace FetchMeditation;

class JFTEntry
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
        return json_encode($this);
    }

    public function getHtml()
    {
        $thought = str_replace('Just for today: ', '', $this->thought);
        $paragraphs = "";

        foreach ($this->content as $c) {
            $paragraphs .= "<p>{$c}</p><br>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Just for Today Meditation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=yes">
    <meta http-equiv="expires" content="-1">
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta charset="UTF-8" />
</head>
<body>
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
        <td align="left"><i>{$this->quote}</i><br><br></td>
    </tr>
    <tr>
        <td align="center">{$this->source}<br><br></td>
    </tr>
    <tr>
        <td align="left">{$paragraphs}</td>
    </tr>
    <tr>
        <td align="left"><b>Just for Today: </b>{$thought}<br><br></td>
    </tr>
    <tr>
        <td align="center">{$this->copyright}</td>
    </tr>
</table>
</body>
</html>
HTML;
    }

    public function getCss()
    {
        $paragraphs = "";
        $count = 1;
        foreach ($this->content as $c) {
            $paragraphs .= "<p id=\"jft-content-$count\" class=\"jft-rendered-element\">$c</p>";
            $count++;
        }

        return <<<CSS
<div id="jft-container" class="jft-rendered-element">
    <div id="jft-date" class="jft-rendered-element">{$this->date}</div>
    <div id="jft-title" class="jft-rendered-element">{$this->title}</div>
    <div id="jft-page" class="jft-rendered-element">{$this->page}</div>
    <div id="jft-quote" class="jft-rendered-element">{$this->quote}</div>
    <div id="jft-quote-source" class="jft-rendered-element">{$this->source}</div>
    <div id="jft-content" class="jft-rendered-element">
    {$paragraphs}
    </div>
    <div id="jft-thought" class="jft-rendered-element">{$this->thought}</div>
    <div id="jft-copyright" class="jft-rendered-element">{$this->copyright}</div>
</div>
CSS;
    }
}
