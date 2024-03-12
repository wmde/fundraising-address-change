<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\ORM\EntityManager;

/**
 * @license GPL-2.0-or-later
 */
class TestEnvironment {

	private TestAddressChangeContextFactory $factory;

	private function __construct() {
		$this->factory = new TestAddressChangeContextFactory(
			[
			'driver' => 'pdo_sqlite',
			'memory' => true,
			]
		);
	}

	public static function newInstance(): self {
		$environment = new self();

		$environment->install();

		return $environment;
	}

	private function install(): void {
		$schema = new SchemaCreator( $this->getEntityManager() );

		try {
			$schema->dropSchema();
		} catch ( \Exception $ex ) {
		}

		$schema->createSchema();
	}

	public function getEntityManager(): EntityManager {
		return $this->factory->getEntityManager();
	}

}
