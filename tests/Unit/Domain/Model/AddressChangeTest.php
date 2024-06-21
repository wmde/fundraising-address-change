<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressType;
use WMDE\Fundraising\AddressChangeContext\Tests\Data\ValidAddress;

#[CoversClass( AddressChange::class )]
class AddressChangeTest extends TestCase {

	private const DUMMY_DONATION_ID = 0;

	private AddressChangeId $identifier;
	private AddressChangeId $newIdentifier;

	public function setUp(): void {
		$this->identifier = AddressChangeId::fromString( 'c956688a-89e8-41b7-b93e-7e4cf3d6c826' );
		$this->newIdentifier = AddressChangeId::fromString( 'e0c4db0b-9049-462c-8c76-c4f6a3a75091' );
	}

	public function testWhenNewAddressChangeIsCreated_itIsNotModified(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$this->assertFalse( $addressChange->isModified() );
	}

	public function testWhenAddressIsUpdated_dataIsProperlyAssigned(): void {
		$addressChange = $this->newPersonAddressChange();
		$address = ValidAddress::newValidPersonalAddress();

		$addressChange->performAddressChange( $address, $this->newIdentifier );

		$this->assertSame( $address, $addressChange->getAddress() );
	}

	private function newPersonAddressChange(): AddressChange {
		return new AddressChange(
			AddressType::Person,
			AddressChange::EXTERNAL_ID_TYPE_DONATION,
			self::DUMMY_DONATION_ID,
			$this->identifier,
			null,
			new \DateTime( '1970-01-01' ),
		);
	}

	public function testUpdatingAddressMarksAddressChangeAsModified(): void {
		$addressChange = $this->newPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();

		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress(), $this->newIdentifier );

		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertTrue( $addressChange->isModified() );
	}

	public function testOptingOutOfReceiptMarksAddressChangeAsModified(): void {
		$addressChange = $this->newPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();

		$addressChange->optOutOfDonationReceipt( $this->newIdentifier );

		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertTrue( $addressChange->isModified() );
	}

	public function testNewAddressChangeIsNotExported(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$this->assertFalse( $addressChange->isExported() );
	}

	public function testAddressChangeCanBeMarkedAsExported(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->markAsExported();
		$this->assertTrue( $addressChange->isExported() );
	}

	public function testAddressChangeCannotBeExportedTwice(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->markAsExported();

		$this->expectException( \LogicException::class );

		$addressChange->markAsExported();
	}

	public function testMarkingAsExportedDoesNotChangeModificationDate(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->markAsExported();
		$this->assertFalse( $addressChange->isModified() );
	}

	public function testWhenAddressChangeIsPerformed_exportStateIsReset(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->markAsExported();
		$addressChange->performAddressChange( ValidAddress::newValidCompanyAddress(), $this->newIdentifier );

		$this->assertFalse( $addressChange->isExported() );
	}

	public function testReferenceTypeIsValidated(): void {
		$this->expectException( \InvalidArgumentException::class );

		new AddressChange( AddressType::Person, 'dogs!', self::DUMMY_DONATION_ID, $this->identifier );
	}

	public function testUnusedAddressReturnsCorrectExportState(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );

		$this->assertEquals( AddressChange::EXPORT_STATE_NO_DATA, $addressChange->getExportState() );
	}

	public function testUsedAddressReturnsCorrectExportState(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress(), $this->newIdentifier );

		$this->assertEquals( AddressChange::EXPORT_STATE_USED_NOT_EXPORTED, $addressChange->getExportState() );
	}

	public function testUsedAndExportedAddressReturnsCorrectExportState(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress(), $this->newIdentifier );
		$addressChange->markAsExported();

		$this->assertEquals( AddressChange::EXPORT_STATE_USED_EXPORTED, $addressChange->getExportState() );
	}

	public function testNewAddressChangesAreUnused(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );

		$this->assertFalse( $addressChange->hasBeenUsed() );
	}

	public function testAddressChangesWithAddressesAreUsed(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress(), $this->newIdentifier );

		$this->assertTrue( $addressChange->hasBeenUsed() );
	}

	public function testAddressChangesWithOptOutAreUsed(): void {
		$addressChange = new AddressChange( AddressType::Person, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $this->identifier );
		$addressChange->optOutOfDonationReceipt( $this->newIdentifier );

		$this->assertTrue( $addressChange->hasBeenUsed() );
	}

	public function testMultipleModificationsWithTheSameIdentifierKeepsIdentifiers(): void {
		$addressChange = $this->newPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();

		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress(), $this->newIdentifier );
		$addressChange->optOutOfDonationReceipt( $this->newIdentifier );

		$this->assertEquals( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertEquals( $this->newIdentifier, $addressChange->getCurrentIdentifier() );
	}
}
