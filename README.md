# Fetch Meditation

[![Packagist Version](https://img.shields.io/packagist/v/bmlt/fetch-meditation)](https://packagist.org/packages/bmlt/fetch-meditation)

* PHP library for reading and parsing daily meditations 

* [Documentation](https://bmlt-enabled.github.io/fetch-meditation/)

* [Packagist](https://packagist.org/packages/bmlt/fetch-meditation)

### Basic Usage
```php
require_once __DIR__ . '/vendor/autoload.php';

use FetchMeditation\JFTLanguage;
use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

$settings = new JFTSettings(JFTLanguage::English);
$jft = JFT::getInstance($settings);
$entry = $jft->fetch();

echo $entry->quote;
```
