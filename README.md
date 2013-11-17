Example
=======

```
require_once __DIR__ . '/vendor/autoload.php';

$client = new \WikiClient\HttpClient( '/tmp' );
$data = $client->get( 'https://www.wikidata.org/w/api.php', array(
    'action' => 'wbgetentities',
    'ids' => 'Q60',
    'format' => 'json'
) );
```
