# ems-php-api
A simple php interface to work with EMS api endpoints.

Sample use:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
/* initialize the ems class with main configs */
use Bledileka\Emsender;

$EMS = new Bledileka\Emsender\Apicall (
	[
		"api_key" => "90C5626330E03D5C1799DF270AF7A114528B6F40", // set your api key
		"api_url" => "http://ems.loc/api/v2/" // set your EMS installation url
	]
);



/* sample call to create a new contact */
$call = [
	"path" => "contacts/add",
	"data" => [
		"listid"=>1,
		"first_name" => "John",
		"last_name" => "Smith",
		"email" => "john@smith.com"
	]
];

$results = $EMS->_call($call);
print_r($results);

?>
```
