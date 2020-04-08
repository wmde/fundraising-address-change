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

	private const DUMMY_DONATION_ID = 0;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	public function setUp(): void {
		$this->entityManager = TestEnvironment::newInstance()->getEntityManager();
	}

	public function testWhenNewAddressChangeIsCreated_uuidIsGenerated(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$this->assertNotEmpty( $addressChange->getCurrentIdentifier() );
	}

	public function testWhenNewAddressChangeIsCreated_itIsNotModified(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$this->assertFalse( $addressChange->isModified() );
	}

	public function testWhenAddressIsUpdated_dataIsProperlyAssigned(): void {
		$addressChange = $this->newPersonAddressChange();
		$address = ValidAddress::newValidPersonalAddress();

		$addressChange->performAddressChange( $address );

		$this->assertSame( $address, $addressChange->getAddress() );
	}

	private function newPersonAddressChange(): AddressChange {
		return $addressChange = new AddressChange(
			AddressChange::ADDRESS_TYPE_PERSON,
			AddressChange::EXTERNAL_ID_TYPE_DONATION,
			self::DUMMY_DONATION_ID,
			null,
			null,
			new \DateTime( '1970-01-01' )
		);
	}

	public function testUpdatingAddressMarksAddressChangeAsModified(): void {
		$addressChange = $this->newPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();

		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress() );

		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertTrue( $addressChange->isModified() );
	}

	public function testAddressChangeCannotBePerformedTwice(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$addressChange->performAddressChange( ValidAddress::newValidCompanyAddress() );

		$this->expectException( \LogicException::class );

		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress() );
	}

	public function testOptingOutOfReceiptMarksAddressChangeAsModified(): void {
		$addressChange = $this->newPersonAddressChange();
		$initialIdentifier = $addressChange->getCurrentIdentifier();

		$addressChange->optOutOfDonationReceipt();

		$this->assertNotSame( $initialIdentifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $initialIdentifier, $addressChange->getPreviousIdentifier() );
		$this->assertTrue( $addressChange->isModified() );
	}

	public function testNewAddressChangeIsNotExported(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$this->assertFalse( $addressChange->isExported() );
	}

	public function testAddressChangeCanBeMarkedAsExported(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$addressChange->markAsExported();
		$this->assertTrue( $addressChange->isExported() );
	}

	public function testAddressChangeCannotBeExportedTwice(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$addressChange->markAsExported();

		$this->expectException( \LogicException::class );

		$addressChange->markAsExported();
	}

	public function testMarkingAsExportedDoesNotChangeModificationDate(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$addressChange->markAsExported();
		$this->assertFalse( $addressChange->isModified() );
	}

	public function testMultipleChangesModifyIdentifiersOnlyOnce(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );

		$originalIdentifier = $addressChange->getCurrentIdentifier();
		$addressChange->performAddressChange( ValidAddress::newValidPersonalAddress() );
		$identifierAfterFirstChange = $addressChange->getCurrentIdentifier();
		$previousIdentifierAfterFirstChange = $addressChange->getPreviousIdentifier();

		$addressChange->optOutOfDonationReceipt();
		$identifierAfterSecondChange = $addressChange->getCurrentIdentifier();
		$previousIdentifierAfterSecondChange = $addressChange->getPreviousIdentifier();

		$this->assertSame( $previousIdentifierAfterFirstChange, $originalIdentifier, 'The original identifier must become the previous identifier' );
		$this->assertSame( $previousIdentifierAfterSecondChange, $previousIdentifierAfterFirstChange, 'The previous identifier must not change after the first modification' );
		$this->assertSame( $identifierAfterFirstChange, $identifierAfterSecondChange, 'The current identifier must not change after the first modification' );
	}

	public function testWhenAddressChangeIsPerformed_exportStateIsReset(): void {
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
		$addressChange->markAsExported();
		$addressChange->performAddressChange( ValidAddress::newValidCompanyAddress() );

		$this->assertFalse( $addressChange->isExported() );
	}

	public function testConstructorAcceptValidUuids(): void {
		$uuid = '72dfed91-fa40-4af0-9e80-c6010ab29cd1';
		$addressChange = new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $uuid );

		$this->assertSame( $uuid, $addressChange->getCurrentIdentifier() );
	}

	/**
	 * @dataProvider invalidUUIDProvider
	 */
	public function testPersonalAddressChangeThrowsExceptionsWhenUUIDIsInvalid( string $invalidUUID ): void {
		$this->expectException( \InvalidArgumentException::class );
		new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, $invalidUUID );
	}

	public function invalidUUIDProvider(): \Generator {
		yield [ '' ];
		yield [ 'just a string' ];
		yield [ '1111222233334444-1111222233334444-1111222233334444-1111222233334444-1111222233334444' ];
		yield [ 'e-f-f-e-d' ];
		yield [ 'This-is-not-a-UUID' ];
	}

	public function testAddressTypeIsValidated(): void {
		$this->expectException( \InvalidArgumentException::class );

		new AddressChange( 'TyPE_Person', AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID );
	}

	public function testReferenceTypeIsValidated(): void {
		$this->expectException( \InvalidArgumentException::class );

		new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, 'dogs!', self::DUMMY_DONATION_ID );
	}
}
