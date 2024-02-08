<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;

/**
 * @license GPL-2.0-or-later
 */
class TestEnvironment {

	private TestAddressChangeContextFactory $factory;

	/**
	 * @param array<mixed> $config
	 * @param Configuration $doctrineConfig
	 *
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function __construct( array $config, Configuration $doctrineConfig ) {
		$this->factory = new TestAddressChangeContextFactory( $config, $doctrineConfig );
	}

	public static function newInstance(): self {
		$contextFactory = new AddressChangeContextFactory();
		$environment = new self(
			[
				'db' => [
					'driver' => 'pdo_sqlite',
					'memory' => true,
				]
			],
			ORMSetup::createXMLMetadataConfiguration( $contextFactory->getDoctrineMappingPaths(), true )
		);

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
