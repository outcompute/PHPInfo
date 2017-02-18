# PHPInfo
A simple PHP library to get the output of [phpinfo()][phpinfoDoc] as an array when invoked from a command line script. You can submit a PR if you adapt it to the HTML version. Although by now you can compile the information to a reasonable degree of completeness from other functions, so this will be of use only when you absolutely have to parse phpinfo().
  - Returns nested information for Configuration, modules in Configuration, Environment, PHP Variables & PHP License

### Installation
Add this line to your composer.json file,
```json
"outcompute/phpinfo": "1.0.0"
```
and run.
```sh
$ composer update
```

### How to use
```php
<?php
include_once('vendor/autoload.php');

ob_start();
phpinfo();
$phpinfoAsString = ob_get_contents();
ob_get_clean();

$phpInfo = new OutCompute\PHPInfo\PHPInfo();
$phpInfo->setText($phpinfoAsString);
var_export($phpInfo->get());
?>
```


### TODO
(PRs are welcome)
 - Add support for parsing the HTML version (the _parseHTML() method)
 - Add test cases


License
----

GPL v2


   [phpinfoDoc]: <http://php.net/manual/en/function.phpinfo.php>
