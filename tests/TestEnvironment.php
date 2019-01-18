<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

/**
 * @license GNU GPL v2+
 */
class TestEnvironment {

	private $config;
	private $factory;

	public static function newInstance(): self {
		$environment = new self(
			[
				'db' => [
					'driver' => 'pdo_sqlite',
					'memory' => true,
				]
			]
		);

		$environment->install();

		return $environment;
	}

	private function __construct( array $config ) {
		$this->config = $config;
		$this->factory = new AddressChangeContextFactory( $this->config );
	}

	private function install(): void {
		$schema = new DatabaseSchema( $this->factory->getEntityManager() );

		try {
			$schema->dropSchema();
		}
		catch ( \Exception $ex ) {
		}

		$schema->createSchema();
	}

	public function getFactory(): AddressChangeContextFactory {
		return $this->factory;
	}

}