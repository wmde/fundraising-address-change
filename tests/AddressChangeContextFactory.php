<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\Setup;

/**
 * @licence GNU GPL v2+
 */
class AddressChangeContextFactory {

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/../config/DoctrineClassMapping';

	private $config;

	/**
	 * @var Connection
	 */
	private $connection;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	public function getEntityManager(): EntityManager {
		$config = Setup::createConfiguration();

		$driver = new XmlDriver( self::DOCTRINE_CLASS_MAPPING_DIRECTORY );
		$config->setMetadataDriverImpl( $driver );

		return EntityManager::create( $this->getConnection(), $config );
	}

	private function getConnection(): Connection {
		if ( $this->connection === null ) {
			$this->connection = DriverManager::getConnection( $this->config['db'] );
		}
		return $this->connection;
	}

}
