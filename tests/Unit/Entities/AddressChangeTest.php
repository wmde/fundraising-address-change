<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Entities;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChange\Entities\Address;
use WMDE\Fundraising\AddressChange\Entities\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Tests\Data\ValidAddress;
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
		$address = ValidAddress::newValidPersonalAddress();
		$addressChange->performAddressChange( $address );

		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $address, $addressChange->getAddress() );
	}

	public function testAddressChangeCannotBePerformedTwice() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$addressChange->performAddressChange( ValidAddress::newValidCompanyAddress() );

		$this->expectException( \LogicException::class );

		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress() );
	}

	public function testNewAddressChangeIsNotExported() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$this->assertFalse( $addressChange->isExported() );
	}

	public function testAddressChangeCanBeMarkedAsExported() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$addressChange->markAsExported();
		$this->assertTrue( $addressChange->isExported() );
	}

	public function testAddressChangeCannotBeExportedTwice() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$addressChange->markAsExported();

		$this->expectException( \LogicException::class );

		$addressChange->markAsExported();
	}

	public function testWhenAddressChangeIsPerformed_exportStateIsReset() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$addressChange->markAsExported();
		$addressChange->performAddressChange( ValidAddress::newValidCompanyAddress() );

		$this->assertFalse( $addressChange->isExported() );
	}
}
