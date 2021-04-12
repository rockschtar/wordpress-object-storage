# rockschtar/wordpress-object-storage

## Description

WordPress [Must Use Plugin](https://codex.wordpress.org/Must_Use_Plugins) that provides functions simliar to 
[transients](https://developer.wordpress.org/apis/handbook/transients/) but without the general [behavior](https://core.trac.wordpress.org/ticket/20316#comment:47) of transients
in WordPress. Stored data persist forever or until expiration time. You do not need to serialize values. If the value needs to be serialized, 
then it will be serialized before it is set. Developed for usage  with composer based WordPress projects
([roots/bedrock](https://github.com/roots/bedrock) or[johnpbloch/wordpress](https://github.com/johnpbloch/wordpress)).

## Requirements

- PHP 7.1
- [Composer](https://getcomposer.org/) to install

## Install

```
composer require rockschtar/wordpress-object-storage
```

## License

rockschtar/wordpress-object-storage is open source and released under MIT
license. See [LICENSE.md](LICENSE.md) file for more info.

## Usage

### Set Object
**php**
```php
//without expiration time
rsos_set_object('my-key', 'my-value');

//with expiration time
rsos_set_object('my-key', 'my-value', 60 * 60 * 24);
```

### Get Object
**php**
```php
$myKey = rsos_get_object('my-key');
```

### Delete Object
**php**
```php
$myKey = rsos_delete_object('my-key');
```

