<?php

use WMDE\Fundraising\AddressChangeContext\Tests\TestEnvironment;

// This file is for providing a correctly-configured Entity Manager object for the PHPStan Doctrine extension
// See https://github.com/phpstan/phpstan-doctrine#configuration

require __DIR__ . '/../vendor/autoload.php';

return TestEnvironment::newInstance()->getEntityManager();
