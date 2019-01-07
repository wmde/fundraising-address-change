<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use WMDE\Fundraising\Store\Factory as StoreFactory;
use WMDE\Fundraising\Store\Installer;

/**
 * @licence GNU GPL v2+
 */
class AddressChangeContextFactory {

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/../config/DoctrineClassMapping';

	private $config;

	private $connection;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function getEntityManager(): EntityManager {
		$config = Setup::createConfiguration();

		$driver = new \Doctrine\ORM\Mapping\Driver\XmlDriver( self::DOCTRINE_CLASS_MAPPING_DIRECTORY );
		$config->setMetadataDriverImpl( $driver );

		$eventManager = $this->getConnection()->getEventManager();

		$entityManager = EntityManager::create( $this->connection, $config, $eventManager );

		$platform = $entityManager->getConnection()->getDatabasePlatform();
		$platform->registerDoctrineTypeMapping( 'enum', 'string' );

		return $entityManager;
	}

	private function getConnection(): Connection {
		if ( $this->connection === null ) {
			$this->connection = DriverManager::getConnection( $this->config['db'] );
		}
		return $this->connection;
	}

	public function newInstaller(): Installer {
		return ( new StoreFactory( $this->getConnection() ) )->newInstaller();
	}

}
