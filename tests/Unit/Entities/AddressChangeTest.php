<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Entities;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChange\Entities\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\AddressChange\Entities\AddressChange
 */
class AddressChangeTest extends TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp(): void {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	public function testWhenNewAddressChangeIsPersisted_uuidIsGenerated() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$this->assertNotEmpty( $addressChange->getCurrentIdentifier() );
	}

	public function testWhenAddressIdentifierIsUpdated_dataIsProperlyAssigned() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();
		$addressChange->performAddressChange();

		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
	}
}
