# rockschtar/wordpress-object-storage

[![CI](https://github.com/rockschtar/wordpress-object-storage/actions/workflows/ci.yml/badge.svg)](https://github.com/rockschtar/wordpress-object-storage/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/rockschtar/wordpress-object-storage)](https://packagist.org/packages/rockschtar/wordpress-object-storage)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)

## Description

WordPress plugin that provides functions similar to
[transients](https://developer.wordpress.org/apis/handbook/transients/) but without their general
[behavior](https://core.trac.wordpress.org/ticket/20316#comment:47): transients may disappear at any
time (for example when a persistent object cache evicts them), so code must never rely on them.
Objects stored with this plugin persist until they expire or are deleted — nothing else removes them.

- Data is stored in the `wp_options` table (never autoloaded), so it survives object cache evictions.
- Optional expiration time per object; expired objects are cleaned up hourly by a WP-Cron job
  (`rsos_delete_expired`) and lazily on read.
- Values are serialized automatically when needed — store scalars, arrays or objects as they are.

## Requirements

- PHP >= 8.4
- WordPress >= 6.8

## Install

### Composer

For composer based WordPress projects ([roots/bedrock](https://github.com/roots/bedrock) or
[johnpbloch/wordpress](https://github.com/johnpbloch/wordpress)); the package is installed as a
must-use plugin (`wordpress-muplugin`):

```
composer require rockschtar/wordpress-object-storage
```

### Manual

Download `wordpress-object-storage-<version>.zip` from the
[latest release](https://github.com/rockschtar/wordpress-object-storage/releases/latest) and install
it like any other plugin (the zip ships with its own autoloader).

## Usage

### Set object

```php
// without expiration time: stored until deleted
rsos_set_object('my-key', 'my-value');

// with expiration time in seconds
rsos_set_object('my-key', 'my-value', DAY_IN_SECONDS);
```

An expiration of `0` (default) means the object never expires. A negative expiration deletes the
object.

### Get object

```php
$value = rsos_get_object('my-key');
```

Returns `false` if the object does not exist or is expired. Like `get_option()`, a stored value of
`false` is indistinguishable from a missing object.

### Delete object

```php
rsos_delete_object('my-key');
```

### ObjectStorage class

The `rsos_*` functions are thin wrappers around the `ObjectStorage` class, which offers a few more
methods:

```php
use Rockschtar\WordPress\ObjectStorage\ObjectStorage;

$storage = new ObjectStorage();

$storage->set('my-key', ['foo' => 'bar'], HOUR_IN_SECONDS);
$storage->get('my-key');                // false|mixed
$storage->delete('my-key');             // bool

$storage->expires('my-key');            // expiration as unix timestamp, null if none
$storage->expiresAsDateTime('my-key');  // expiration as DateTime (site timezone), null if none
$storage->getItem('my-key');            // ObjectStorageItem with key, value and expiration

$storage->deleteExpired();              // delete all expired objects (what the cron job runs)
$storage->clear();                      // delete ALL stored objects, expired or not
```

## License

rockschtar/wordpress-object-storage is open source and released under MIT license.
See [LICENSE.md](LICENSE.md) file for more info.
