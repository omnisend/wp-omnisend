# Omnisend Core Plugin

[Omnisend](https://wordpress.org/plugins/omnisend/) is WordPress plugin that enables to use [Omnisend marketing automation tool](https://www.omnisend.com/) features
for WordPress website.

## Plugin features

* Website connection to Omnisend [features](https://www.omnisend.com/features/))
* Client to connect other plugins to Omnisend.


## Client

The plugin provides an SDK client to easily integrate with Omnisend API.

> [!IMPORTANT]  
> To use in your plugin you must check if wp-omnisend plugin is installed.
> Provided client will send data to Omnisend if it is connected.

You can find function references in the [client folder](https://github.com/omnisend/wp-omnisend/tree/main/omnisend/includes/Public/Client/V1).

### Examples

To create a contact:

```php
TBD check if plugin is activated
TBD create client
TBD create contact
TBD send contact
```

## PHP Linting

WordPress.org team mandates our plugin to be linted
against [WordPress coding standards](https://github.com/WordPress/WordPress-Coding-Standards).

After each push to any branch `PHP Standards` action will run and all the PHP code will be linted. See action output for results.

### Linting locally

Tools needed:

-   php (7.4 version is recommended because at the time of writing WordPress coding standards support only up to 7.4 version);
-   composer (can be installed as described in https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos);

After installing those tools one can run in local plugin dir (plugin_woocommerce_api) helper script:

```shell
./lint.sh check
./lint.sh fix
```

or all commands manually. Following commands

```shell
composer update
composer install
```

install linting tool and standards. Then actual linting `phpcs` script can be initiated with

```shell
./vendor/squizlabs/php_codesniffer/bin/phpcs --ignore=.js --standard=WordPress omnisend-connect
```

A second `phpcbf` script can be run to automatically correct coding standard violations:

```shell
./vendor/squizlabs/php_codesniffer/bin/phpcbf --ignore=.js --standard=WordPress omnisend-connect
```
