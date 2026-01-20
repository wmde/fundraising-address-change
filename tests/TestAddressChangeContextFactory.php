<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;

/**
 * @phpstan-import-type Params from DriverManager
 */
class TestAddressChangeContextFactory {

	private Configuration $doctrineConfig;
	private ?EntityManager $entityManager;
	private AddressChangeContextFactory $contextFactory;

	/**
	 * @param Params $config
	 */
	public function __construct( private readonly array $config ) {
		$this->contextFactory = new AddressChangeContextFactory();
		$this->doctrineConfig = ORMSetup::createXMLMetadataConfiguration(
			$this->contextFactory->getDoctrineMappingPaths(),
			true
		);
		$this->doctrineConfig->enableNativeLazyObjects( true );

		$this->entityManager = null;
	}

	private function newConnection(): Connection {
		$connection = DriverManager::getConnection( $this->config );
		$this->contextFactory->registerCustomTypes( $connection );
		return $connection;
	}

	public function newEntityManager(): EntityManager {
		return new EntityManager( $this->newConnection(), $this->doctrineConfig );
	}

	public function getEntityManager(): EntityManager {
		if ( $this->entityManager === null ) {
			$this->entityManager = $this->newEntityManager();
		}
		return $this->entityManager;
	}
}
