<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;

class TestAddressChangeContextFactory {

	private Configuration $doctrineConfig;
	private Connection $connection;
	private ?EntityManager $entityManager;

	/**
	 * @param array<string,mixed> $config
	 * @param Configuration $doctrineConfig
	 *
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function __construct( array $config, Configuration $doctrineConfig ) {
		$this->doctrineConfig = $doctrineConfig;

		$this->connection = DriverManager::getConnection( $config['db'] );
		$this->entityManager = null;
	}

	public function getEntityManager(): EntityManager {
		if ( $this->entityManager === null ) {

			$this->entityManager = new EntityManager(
				$this->connection,
				$this->doctrineConfig
			);
		}

		return $this->entityManager;
	}
}
