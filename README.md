# Time2Configure

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Latest Stable Version](https://poser.pugx.org/time2split/time2help/v)](https://packagist.org/packages/time2split/time2configure)
[![Latest Unstable Version](https://poser.pugx.org/time2split/time2help/v/unstable)](https://packagist.org/packages/time2split/time2configure)

Time2Configure is a php library implementing the concept of tree configuration with value interpolations.

Configuring a program consists to get some entries from the external world, for instance by entering some command line arguments, and then to use theses entries inside the program.
Most of the time, the program does the reading of a formatted configuration (`json`, `ini`, etc) with some specialized format reader and then uses directly the obtained result as the program configuration.
Usefull formats for configuration storage may be `json`, `php` array, or other tree shapped formats.
Even a flat format like `ini` or `csv` can allows the usage of a hierarchical notation (eg. ```parent.child```) that finally defines a tree-shapped configuration.
Tree is a natural way to represent a configuration.

Time2Configure proposes a general abstraction of [tree configuration](https://time2split.net/php-time2configure/classes/Time2Split-Config-Configuration.html), not focused on any format but rather on storage and features.
 Moreover, these tree configurations have the very usefull ability to use complex value interpolation languages.

## Installation

The library is distributed as a Composer project.

```bash
$ composer require time2split/time2configure
```

## Documentation

 * [API documentation](https://time2split.net/php-time2configure)

## Some examples

To have a very first taste of the library in action let's give a first example:
```php
use Time2Split\Config\Configurations;

// Some configuration possibly loaded from an external file
$loadedConfig = [
    'text' => [
        'locale' => 'en_US',
        'encoding' => 'UTF-8'
    ],
    'database' => [
        'driver' => 'mysql',
        'port' => 3306,
        'database' => 'db',
        'username' => 'zuri',
        'password' => 'xxx',
    ],
];
$config = Configurations::ofTree($loadedConfig);

echo $config['text.locale'], "\n";
echo $config['database.driver'], "\n";
```
```bash
### Output ###
en_US
mysql
```

Because the configuration is a tree we are able reduce the visibility of some part of a program to a specific sub-tree.
```php
$view = $config->subTreeView('database');
print_r($view->toArray());
```
```bash
### Output ###
Array
(
    [driver] => mysql
    [port] => 3306
    [database] => db
    [username] => zuri
    [password] => xxx
)
```

The library consider that each node of the tree can store a value, not only leaves.
```php
$config['database.driver'] = 'sqlite';
$config['database'] = 'an internal value';
print_r($config->toArray());
```
```bash
### Output ###
Array(
    [text.locale] => en_US
    [text.encoding] => UTF-8
    [database] => an internal value
    [database.driver] => sqlite
    [database.port] => 3306
    [database.database] => db
    [database.username] => zuri
    [database.password] => xxx
)
```

## Value interpolation

The second main feature, over the tree aspect is the value interpolation.

Value interpolation permits to compile automatically an entries's value to generate dynamically another value when the entry is accessed.
Here is an example using the previous example's $config:
```php
// Get a new configuration instance with an interpolator
$iconfig = Configurations::treeCopyOf($config, Interpolators::recursive());

$iconfig['interpolated'] = '${text.locale} and ${text.encoding}';
echo $iconfig['interpolated'], "\n";

$iconfig['text.locale'] = 'UTF-16';
echo $iconfig['interpolated'], "\n";
```
```bash
### Output ###
en_US and UTF-8
UTF-16 and UTF-8
```
In this example we used the provided interpolator
[Interpolators::recursive()](https://time2split.net/php-time2configure/classes/Time2Split-Config-Interpolators.html)
which substitute every ${key} token encountered in a text value by the actual $config['key'] value stored.
(For now, this is the only interpolator provided by the library.)

More complex interpolated language can be made.
For instance, the
[pcp](https://github.com/time2split/pcp)
project defines a more complex language with the help of the great
[parsica-php/parsica](https://github.com/parsica-php/parsica) project.
The 
[pcp interpolated language]()
 permits to use operators based expressions (assignments, boolean)
 and is even able to parse command line arguments.

 The interpolation principe is simple, but let the ability to create very complex languages.

## Features

 - [Tree shapped configurations](https://time2split.net/php-time2configure/packages/time2configure-configuration.html)
 - [Fluent tree builder](https://time2split.net/php-time2configure/classes/Time2Split-Config-TreeConfigurationBuilder.html)  
 - [Interpolated values](https://time2split.net/php-time2configure/packages/time2configure-interpolation.html)
 - [Hierarchical configurations](https://time2split.net/php-time2configure/classes/Time2Split-Config-Configurations.html#method_hierarchy)
 - [Operations and decorators to add more complex behaviours](https://time2split.net/php-time2configure/classes/Time2Split-Config-Configurations.html)

### Hierarchy of configurations
 
 In many scenarios there is a default base configuration that the user can modify to create the final one.
 Time2Configure provides a solution to create a list of configurations where only the last one can be effectively modified.
 ```php
$default = [
    'text' => [
        'locale' => 'en_US',
        'encoding' => 'UTF-8'
    ]
];
$default = Configurations::ofTree($default);
/*
    $default is the config to search for
    the unexistant entries of $config.
*/
$config = Configurations::emptyChild($default);

echo $config['text.locale'], "\n";

$config['text.locale'] = 'override';
echo $config['text.locale'], "\n";

$config['text.locale'] = null;
echo $config['text.locale'], "\n";

unset($config['text.locale']);
echo $config['text.locale'], "\n";
 ```
 ```bash
### Output ###
 en_US
override

en_US
 ```
 More generally, this is a very usefull feature that permits to a process to immunize its configuration against modification by a sub-process without the need of copying.

 ### Decorators

 Decorators are usefull to add more behaviour to a configuration.
 Fo instance, the decorator
 [Configurations::doOnRead()](https://time2split.net/php-time2configure/classes/Time2Split-Config-Configurations.html#method_doOnRead)
 is able to do an action when a value is accessed.
 ```php
$tree = [
    'text' => [
        'locale' => 'en_US',
        'encoding' => 'UTF-8'
    ]
];
$config = Configurations::ofTree($tree);
$doEcho = Entries::consumeEntry(function ($key, $val) {
    echo "$key=$val\n";
});
$echoConfig = Configurations::doOnRead($config, $doEcho);

$echoConfig['text.locale'];
iterator_to_array($echoConfig);
```
 ```bash
### Output ###
text.locale=en_US
text.locale=en_US
text.encoding=UTF-8
 ```

 There is also some mapping decorators that can uses the original entry values to makes new one.
 Here is a new examples:

 ```php
$config = Configurations::ofTree();
$config['url.php'] = 'https://www.php.net/support';

// Dereference an url and retrieves the header
$getHeader = Entries::mapValue(function ($value, $key) {

    if (\str_starts_with($key, 'url'))
        return \get_headers($value, true);

    return $value;
});
$deref = Configurations::mapOnRead($config, $getHeader);

print_r($deref['url.php']);
```
 ```bash
### Output ###
Array
(
    [0] => HTTP/1.1 200 OK
    [Server] => myracloud
    [Date] => Mon, 08 Apr 2024 20:00:43 GMT
    [Content-Type] => text/html; charset=utf-8
    [Transfer-Encoding] => chunked
    [Connection] => close
    [Content-language] => en
    [Permissions-Policy] => interest-cohort=()
    [X-Frame-Options] => SAMEORIGIN
    [Link] => <https://www.php.net/support>; rel=shorturl
    [Expires] => Mon, 08 Apr 2024 20:00:43 GMT
    [Cache-Control] => max-age=0
    [ETag] => "myra-3087d513"
)
```