# Data Wizard tests

Run the tests in a configured OXID shop installation with the module activated and the development dependencies from `composer.json` available. The PHPUnit configuration bootstraps `source/bootstrap.php`; the test environment therefore needs the shop configuration and its required services. The installed shop's root Composer configuration determines which development dependencies are installed.

Run commands from the shop root, which contains `source` and `vendor`. The examples below use the package-specific configuration; the command examples in the package's `composer.json` are under `extra.commands`, not Composer `scripts`.

```bash
./vendor/bin/phpunit --configuration=vendor/d3/datawizard/.ci-tools/phpunit.xml --no-coverage
```

To generate line coverage, use a PHP runtime with a supported coverage driver enabled, for example Xdebug:

```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --configuration=vendor/d3/datawizard/.ci-tools/phpunit.xml --coverage-html=reports/phpunit/datawizard --coverage-clover=reports/phpunit/datawizard/clover.xml
```

For mutation testing, install Infection and run:

```bash
XDEBUG_MODE=coverage ./vendor/bin/infection --configuration=vendor/d3/datawizard/.ci-tools/infection.json --threads=1
```

If PHP or the required services run remotely, verify that the working-tree changes have been deployed to that runtime before interpreting the results. The generated reports belong to the runtime on which the commands execute.
