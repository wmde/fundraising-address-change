<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;

class TestAddressChangeContextFactory {

	private Configuration $doctrineConfig;
	private ?EntityManager $entityManager;
	private AddressChangeContextFactory $contextFactory;

	/**
	 * @param array<string,mixed> $config
	 *
	 * @throws Exception
	 */
	public function __construct( private array $config ) {
		$this->contextFactory = new AddressChangeContextFactory();
		$this->doctrineConfig = ORMSetup::createXMLMetadataConfiguration(
			$this->contextFactory->getDoctrineMappingPaths(),
			true
		);
		$this->entityManager = null;
	}

	private function newConnection(): Connection {
		$connection = DriverManager::getConnection( $this->config['db'] );
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
