# Fetch Meditation

* PHP library for reading and parsing daily meditations 

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
