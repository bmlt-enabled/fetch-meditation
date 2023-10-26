# Fetch Meditation

* PHP library for reading and parsing daily meditations 

### Basic Usage
```php
require_once __DIR__ . '/vendor/autoload.php';

use FetchMeditation\JFTSettings;
use FetchMeditation\JFT;

echo "JFT\n\n";

$settings = new JFTSettings(['language' => "en"]);
$jft = new JFT($settings);
$entry = $jft->fetch();
echo $entry->quote;
```
