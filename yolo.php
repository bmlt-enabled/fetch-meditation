<?php

$langs = ['da', 'de', 'en', 'es', 'fa', 'fr', 'it', 'pl', 'pt', 'ru', 'sv'];
$content = [];

foreach ($langs as $lang) {
    $daysOfWeek = [];
    $langName = \Locale::getDisplayLanguage($lang, $lang);
    $langNameEn = \Locale::getDisplayLanguage($lang, 'en');
    if (strlen($langName) < 3) {
        // If locale is invalid getDisplayLanguage just returns the provided locale
        // So this is how we handle errors as any language should have more than two chars
        // Skip language and move to the next one.
        continue;
    }
    // Capitalize the first letter of the language name
    $firstChar = mb_substr($langName, 0, 1, "utf-8");
    $then = mb_substr($langName, 1, null, "utf-8");
    $langName = mb_strtoupper($firstChar, "utf-8") . $then;

    // Populate the language data
    $content[$lang]['name'] = $langName;
    $content[$lang]['code'] = $lang;
    $content[$lang]['en_name'] = $langNameEn;

    // Populate the days of the week
    for ($i = 0; $i < 7; $i++) {
        $dateTime = new \DateTime("Sunday +{$i} days");
        $day = ucfirst(\IntlDateFormatter::formatObject($dateTime, 'cccc', $lang));
        // BMLT and all code that uses this function expects 1 to be Sunday
        $daysOfWeek[$i + 1] = $day;
    }

    $content[$lang]['days_of_week'] = $daysOfWeek;
}
print_r(json_encode($content));
