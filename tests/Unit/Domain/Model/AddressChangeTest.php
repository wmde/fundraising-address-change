<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Domain\Model;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Tests\Data\ValidAddress;
use WMDE\Fundraising\AddressChangeContext\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\AddressChange\Domain\Model\AddressChange
 */
class AddressChangeTest extends TestCase {

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp(): void {
		$this->entityManager = TestEnvironment::newInstance()->getFactory()->getEntityManager();
	}

	public function testWhenNewAddressChangeIsCreated_uuidIsGenerated() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$this->assertNotEmpty( $addressChange->getCurrentIdentifier() );
	}

	public function testWhenNewAddressChangeIsCreated_itIsNotModified() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$this->assertFalse( $addressChange->isModified() );
	}

	public function testWhenAddressIsUpdated_dataIsProperlyAssigned() {
		$addressChange = $this->newPersonAddressChange();
		$address = ValidAddress::newValidPersonalAddress();

		$addressChange->performAddressChange( $address );

		$this->assertSame( $address, $addressChange->getAddress() );
	}

	private function newPersonAddressChange(): AddressChange {
		return AddressChange::createNewPersonAddressChange( null, null, false, new \DateTime( '1970-01-01' ) );
	}

	public function testUpdatingAddressMarksAddressChangeAsModified() {
		$addressChange = $this->newPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();

		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress() );

		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertTrue( $addressChange->isModified() );
	}

	public function testAddressChangeCannotBePerformedTwice() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$addressChange->performAddressChange( ValidAddress::newValidCompanyAddress() );

		$this->expectException( \LogicException::class );

		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress() );
	}

	public function testOptingOutOfReceiptMarksAddressChangeAsModified() {
		$addressChange = $this->newPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();

		$addressChange->optOutOfDonationReceipt( false );

		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertTrue( $addressChange->isModified() );
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

	public function testMarkingAsExportedDoesNotChangeModificationDate() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$addressChange->markAsExported();
		$this->assertFalse( $addressChange->isModified() );
	}

	public function testMultipleChangesModifyIdentifiersOnlyOnce() {
		$addressChange = AddressChange::createNewPersonAddressChange();

		$originalIdentifier = $addressChange->getCurrentIdentifier();
		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress() );
		$identifierAfterFirstChange = $addressChange->getCurrentIdentifier();
		$previousIdentifierAfterFirstChange = $addressChange->getPreviousIdentifier();

		$addressChange->optOutOfDonationReceipt( false );
		$identifierAfterSecondChange = $addressChange->getCurrentIdentifier();
		$previousIdentifierAfterSecondChange = $addressChange->getPreviousIdentifier();

		$this->assertSame( $previousIdentifierAfterFirstChange, $originalIdentifier, 'The original identifier must become the previous identifier' );
		$this->assertSame( $previousIdentifierAfterSecondChange, $previousIdentifierAfterFirstChange, 'The previous identifier must not change after the first modification' );
		$this->assertSame( $identifierAfterFirstChange, $identifierAfterSecondChange, 'The current identifier must not change after the first modification' );
	}

	public function testWhenAddressChangeIsPerformed_exportStateIsReset() {
		$addressChange = AddressChange::createNewPersonAddressChange();
		$addressChange->markAsExported();
		$addressChange->performAddressChange( ValidAddress::newValidCompanyAddress() );

		$this->assertFalse( $addressChange->isExported() );
	}

	public function testConstructorsAcceptValidUuids() {
		$uuid = '72dfed91-fa40-4af0-9e80-c6010ab29cd1';
		$personAddressChange = AddressChange::createNewPersonAddressChange( $uuid );
		$companyAddressChange = AddressChange::createNewCompanyAddressChange( $uuid );

		$this->assertSame( $uuid, $personAddressChange->getCurrentIdentifier() );
		$this->assertSame( $uuid, $companyAddressChange->getCurrentIdentifier() );
	}

	/**
	 * @dataProvider invalidUUIDProvider
	 */
	public function testPersonalAddressChangeThrowsExceptionsWhenUUIDIsInvalid( string $invalidUUID ) {
		$this->expectException( \InvalidArgumentException::class );
		AddressChange::createNewPersonAddressChange( $invalidUUID );
	}

	/**
	 * @dataProvider invalidUUIDProvider
	 */
	public function testCompanyAddressChangeThrowsExceptionsWhenUUIDIsInvalid( string $invalidUUID ) {
		$this->expectException( \InvalidArgumentException::class );
		AddressChange::createNewCompanyAddressChange( $invalidUUID );
	}

	public function invalidUUIDProvider(): \Generator {
		yield [ '' ];
		yield [ 'just a string' ];
		yield [ '1111222233334444-1111222233334444-1111222233334444-1111222233334444-1111222233334444' ];
		yield [ 'e-f-f-e-d' ];
		yield [ 'This-is-not-a-UUID' ];
	}
}