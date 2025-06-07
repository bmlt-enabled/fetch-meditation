BMLT Fetch Meditation Guide
========================

Installation
-----------

Install via composer::

    composer require bmlt/fetch-meditation

Basic Usage
----------

Here's a basic example of fetching a meditation::

    use FetchMeditation\JFTLanguage;
    use FetchMeditation\JFTSettings;
    use FetchMeditation\JFT;

    // Initialize settings with your preferred language
    $settings = new JFTSettings(JFTLanguage::English);

    // Get JFT instance and fetch today's meditation
    $jft = JFT::getInstance($settings);
    $data = $jft->fetch();

    // Access meditation components:
    echo $data->date;      // Today's date
    echo $data->title;     // Meditation title
    echo $data->page;      // Page reference
    echo $data->quote;     // Quote text
    echo $data->source;    // Quote source
    echo $data->content;   // Main content
    echo $data->thought;   // Just for Today thought
    echo $data->copyright; // Copyright info

Language Support
--------------

The library supports multiple languages::

    // Available languages:
    $settings = new JFTSettings(JFTLanguage::English);    // English
    $settings = new JFTSettings(JFTLanguage::Spanish);    // Spanish
    $settings = new JFTSettings(JFTLanguage::French);     // French
    $settings = new JFTSettings(JFTLanguage::German);     // German
    $settings = new JFTSettings(JFTLanguage::Italian);    // Italian
    $settings = new JFTSettings(JFTLanguage::Japanese);   // Japanese
    $settings = new JFTSettings(JFTLanguage::Portuguese); // Portuguese
    $settings = new JFTSettings(JFTLanguage::Russian);    // Russian
    $settings = new JFTSettings(JFTLanguage::Swedish);    // Swedish

Display Example
-------------

Here's an example of displaying a complete meditation with HTML formatting::

    // Display a complete meditation
    $settings = new JFTSettings(JFTLanguage::English);
    $jft = JFT::getInstance($settings);
    $data = $jft->fetch();

    echo "<h1>{$data->title}</h1>\n";
    echo "<p><em>{$data->date}</em></p>\n";
    echo "<blockquote>{$data->quote}</blockquote>\n";
    echo "<cite>{$data->source}</cite>\n";
    echo "<div class='content'>{$data->content}</div>\n";
    echo "<p class='thought'>{$data->thought}</p>\n";
    echo "<footer>{$data->copyright}</footer>\n";

Available Methods
---------------

JFTSettings
~~~~~~~~~~

- ``__construct(JFTLanguage $language)`` - Create settings with specified language
- ``getLanguage()`` - Get the current language setting

JFT
~~~

- ``getInstance(JFTSettings $settings)`` - Get JFT instance with settings
- ``fetch()`` - Fetch today's meditation
- ``fetchByDate(string $date)`` - Fetch meditation for specific date

JFTEntry
~~~~~~~~

Properties available in the meditation entry:

- ``date`` - Today's date
- ``title`` - Meditation title
- ``page`` - Page reference
- ``quote`` - Quote text
- ``source`` - Quote source
- ``content`` - Main content
- ``thought`` - Just for Today thought
- ``copyright`` - Copyright information 