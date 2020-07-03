<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\AddressChangeContext\AddressChangeContextFactory;

class TestAddressChangeContextFactory {

	private Configuration $doctrineConfig;
	private AddressChangeContextFactory $factory;
	private Connection $connection;
	private ?EntityManager $entityManager;

	public function __construct( array $config, Configuration $doctrineConfig ) {
		$this->doctrineConfig = $doctrineConfig;

		$this->connection = DriverManager::getConnection( $config['db'] );
		$this->factory = new AddressChangeContextFactory();
		$this->entityManager = null;
	}

	public function getEntityManager(): EntityManager {
		if ( $this->entityManager === null ) {
			AnnotationRegistry::registerLoader( 'class_exists' );

			$this->doctrineConfig->setMetadataDriverImpl( $this->factory->newMappingDriver() );

			$this->entityManager = EntityManager::create(
				$this->connection,
				$this->doctrineConfig
			);
		}

		return $this->entityManager;
	}
}
