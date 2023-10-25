<?php

$jft_url = 'https://jftna.org/jft/';
libxml_use_internal_errors(true);
$url = get($jft_url);
libxml_clear_errors();
libxml_use_internal_errors(false);

$domDocument = new DOMDocument();
$domDocument->validateOnParse = true;
$domDocument->loadHTML($url);
$jftKeys = array('date', 'title', 'page', 'quote', 'source', 'content', 'thought', 'copyright');
$result = [];
$xpath = new DOMXPath($domDocument);
foreach ($domDocument->getElementsByTagName('tr') as $i => $element) {
    $formattedElement = trim($element->nodeValue);
    if ($i == 5) {
        $values = [];
        foreach ($xpath->query('//tr') as $row) {
            $rowValues = [];
            foreach ($xpath->query('td', $row) as $cell) {
                $innerHTML = '';
                $children = $cell->childNodes;
                foreach ($children as $child) {
                    $innerHTML .= $child->ownerDocument->saveXML($child);
                }
                $rowValues[] = $innerHTML;
            }
            $values[] = $rowValues;
        }
        $content = preg_split('/<br\s*\/?>/', $values[5][0], -1, PREG_SPLIT_NO_EMPTY);
        $result["content"] = array_map('trim', $content);
    } else {
        $result[$jftKeys[$i]] = $formattedElement;
    }
}
if (array_key_exists('divider', $result)) {
    unset($result['divider']);
}
$result["copyright"] = preg_replace('/\s+/', ' ', str_replace("\n", "", $result["copyright"]));

echo json_encode($result, JSON_PRETTY_PRINT);


function get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $errorno = curl_errno($ch);
    curl_close($ch);
    if ($errorno > 0) {
        throw new Exception(curl_strerror($errorno));
    }
    return $data;
}
