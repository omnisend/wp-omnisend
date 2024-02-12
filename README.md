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

You can find function references in the [client folder](https://github.com/omnisend/wp-omnisend/tree/main/omnisend/includes/Public/V1).

### Examples

#### Ensuring that you can use Client 

Before using Omnisend Client you need to ensure the following conditions:
* Omnisend Plugin is installed `is_plugin_active( 'omnisend/class-omnisend-core-bootstrap.php' )`
* Omnisend Plugin is up to date `class_exists( 'Omnisend\Sdk\V1\Omnisend' )`
* Omnisend is connected to account `Omnisend\Sdk\V1\Omnisend::is_connected()`

If any of these conditions are false you should ask to resolve them.

#### Client Initialization

To send contact to the Omnisend you need to provide your integration name & version. 

This is done by getting an actual client

` $client = \Omnisend\Sdk\V1\Omnisend::get_client( 'integration name', 'integration version' );`

'integration name' - should be your integration name
'integration version' - should be your integration version

#### Contact Creation
Here is how you can create a basic client & submit contact.

```php
		$contact  = new Contact();

		$contact->set_email( $email );
		if ( $phone_number != '' ) {
			$contact->set_phone( $phone_number );
		}
		$contact->set_first_name( $first_name );
		$contact->set_last_name( $last_name );
		$contact->set_birthday( $birthday );
		$contact->set_postal_code( $postal_code );
		$contact->set_address( $address );
		$contact->set_state( $state );
		$contact->set_country( $country );
		$contact->set_city( $city );
		if ( $email_consent ) {
			$contact->set_email_consent( 'actual_email_consent_for_gdrp' );
			$contact->set_email_opt_in( 'where user opted to become subscriber' );
		}

        $client = \Omnisend\Sdk\V1\Omnisend::get_client( 'integration name', 'integration version' );
                    
        $response = $client->create_contact( $contact );
```

#### Error handling

If data provided is invalid or creation fails, then

```php
$response = $client->create_contact($contact)
```

Will return `CreateContactResponse`. Depending on your integration logic you may handle the error i.e

```php
    if ( $response->get_wp_error()->has_errors() ) {
        error_log( 'Error in after_submission: ' . $response->get_wp_error()->get_error_message());
        return;
    }
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
