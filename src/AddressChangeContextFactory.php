<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @license GPL-2.0-or-later
 */
class AddressChangeContextFactory {

	/**
	 * Use this constant for MappingDriverChain::addDriver
	 */
	public const ENTITY_NAMESPACE = 'WMDE\Fundraising\AddressChangeContext\Domain\Model';

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/../config/DoctrineClassMapping';

	/**
	 * @return string[]
	 */
	public function getDoctrineMappingPaths(): array {
		return [ self::DOCTRINE_CLASS_MAPPING_DIRECTORY ];
	}

	/**
	 * @return array<EventSubscriber>
	 */
	public function newEventSubscribers(): array {
		// Currently we don't have event subscribers, but this method helps to keep a consistent interface
		// with all the other context factories of the bounded contexts.
		return [];
	}

	public function registerCustomTypes( Connection $connection ): void {
		$this->registerDoctrinePaymentIntervalType( $connection );
	}

	public function registerDoctrinePaymentIntervalType( Connection $connection ): void {
		static $isRegistered = false;
		if ( $isRegistered ) {
			return;
		}
		Type::addType( 'AddressType', 'WMDE\Fundraising\AddressChangeContext\DataAccess\DoctrineTypes\AddressType' );
		$connection->getDatabasePlatform()->registerDoctrineTypeMapping( 'AddressType', 'AddressType' );
		$isRegistered = true;
	}
}
