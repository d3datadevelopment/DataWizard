# D3 Datawizard Tests

## Requirements

Please install the packages listed in the composer.json in "require-dev". Unfortunately Composer does not provide an automatic installation.
missing ext-sockets

### Configuration

Make sure the module has been activated with a valid license key.
Configure the error reporting to `error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);` in the `config.inc.php` due some deprecation issues in original OXID code

## Unit Tests

commands are described in composer.json scripts section