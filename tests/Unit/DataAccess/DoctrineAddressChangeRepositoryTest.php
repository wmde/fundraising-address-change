<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\DataAccess;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\DataAccess\DoctrineAddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\Address;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;
use WMDE\Fundraising\AddressChangeContext\Tests\TestEnvironment;

#[CoversClass( DoctrineAddressChangeRepository::class )]
class DoctrineAddressChangeRepositoryTest extends TestCase {

	private const VALID_UPDATE_TOKEN_PERSONAL_DONATION = '2a54c0a1-fc94-4ef8-8b0a-7c2ed8565521';
	private const VALID_UPDATE_TOKEN_COMPANY_DONATION = 'c52258ba-fed1-476a-a7e5-c721df087c12';
	private const INVALID_UPDATE_TOKEN = '72dfed91-fa40-4af0-9e80-c6010ab29cd1';
	private const DUMMY_DONATION_ID = 0;

	/** @var EntityManager */
	private $em;

	public function setUp(): void {
		$this->em = TestEnvironment::newInstance()->getEntityManager();
		parent::setUp();
	}

	public function testGivenValidPersonalDonationUuid_addressChangeIsReturned(): void {
		$this->storeAddressChange( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION, true );
		$retrievedAddressChange = $this->retrieveAddressChangeByUuid( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION );

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_PERSONAL_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()->__toString()
		);
		$this->assertTrue( $retrievedAddressChange->isPersonalAddress() );
	}

	public function testGivenValidCompanyDonationUuid_addressChangeIsReturned(): void {
		$this->storeAddressChange( self::VALID_UPDATE_TOKEN_COMPANY_DONATION, false );
		$retrievedAddressChange = $this->retrieveAddressChangeByUuid( self::VALID_UPDATE_TOKEN_COMPANY_DONATION );

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_COMPANY_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()->__toString()
		);
		$this->assertTrue( $retrievedAddressChange->isCompanyAddress() );
	}

	public function testGivenInvalidDonationUuid_nullIsReturned(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = $addressChangeRepository->getAddressChangeByUuid( self::INVALID_UPDATE_TOKEN );
		$this->assertNull( $addressChange );
	}

	public function testGivenAddressChangeWithAddress_itIsStoredCorrectly(): void {
		$donationId = 99;
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create( null, $this->newPersonalAddress() )->forPerson()->forDonation( $donationId )->build();
		$addressChangeRepository->storeAddressChange( $addressChange );
		$now = new \DateTime();

		$this->em->clear();
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( (string)$addressChange->getCurrentIdentifier() );
		$this->assertNotNull( $retrievedAddressChange );
		$this->assertNotNull( $retrievedAddressChange->getAddress() );
		// avoid PHPStan errors when accessing address later
		$this->assertNotNull( $addressChange->getAddress() );
		$this->assertTrue( $retrievedAddressChange->isPersonalAddress() );
		$this->assertSame( $donationId, $retrievedAddressChange->getExternalId() );
		$this->assertSame( AddressChange::EXTERNAL_ID_TYPE_DONATION, $retrievedAddressChange->getExternalIdType() );
		$this->assertFalse( $retrievedAddressChange->isExported() );
		$this->assertDatePropertyIsSet( $now, $retrievedAddressChange, 'createdAt' );
		$this->assertDatePropertyIsSet( $now, $retrievedAddressChange, 'modifiedAt' );
		$this->assertEquals( $addressChange->getCurrentIdentifier(), $retrievedAddressChange->getCurrentIdentifier() );
		$this->assertNotNull( $retrievedAddressChange->getAddress() );
		$this->assertSame( $addressChange->getAddress()->getLastName(), $retrievedAddressChange->getAddress()->getLastName() );
	}

	public function testGivenAddressChangeWithReceiptOptIn_optInIsStoredCorrectly(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create()->forCompany()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChangeRepository->storeAddressChange( $addressChange );

		$this->em->clear();
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( (string)$addressChange->getCurrentIdentifier() );
		$this->assertNotNull( $retrievedAddressChange );
		$this->assertTrue( $retrievedAddressChange->isOptedIntoDonationReceipt() );
	}

	public function testGivenAddressChangeWithReceiptOptOut_optOutIsStoredCorrectly(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create()->forCompany()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChange->optOutOfDonationReceipt( AddressChangeId::fromString( AddressChangeBuilder::generateUuid() ) );
		$addressChangeRepository->storeAddressChange( $addressChange );

		$this->em->clear();
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( (string)$addressChange->getCurrentIdentifier() );
		$this->assertNotNull( $retrievedAddressChange );
		$this->assertFalse( $retrievedAddressChange->isOptedIntoDonationReceipt() );
	}

	public function testGivenExportedAddressChange_itsStateIsStoredCorrectly(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create( null, $this->newPersonalAddress() )->forPerson()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChange->markAsExported();
		$addressChangeRepository->storeAddressChange( $addressChange );
		$now = new \DateTime();

		$this->em->clear();
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( (string)$addressChange->getCurrentIdentifier() );
		$this->assertNotNull( $retrievedAddressChange );
		$this->assertTrue( $retrievedAddressChange->isExported() );
		$this->assertDatePropertyIsSet( $now, $retrievedAddressChange, 'exportDate' );
	}

	public function testWhenQueriedByMultipleUuids_andGivenValidPreviousUuid_addressChangeIsReturned(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create(
			AddressChangeId::fromString( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION ),
			$this->newPersonalAddress()
		)->forPerson()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChangeRepository->storeAddressChange( $addressChange );

		$this->assertNotNull( $addressChange->getPreviousIdentifier() );
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuids(
			self::INVALID_UPDATE_TOKEN,
			$addressChange->getPreviousIdentifier()->__toString()
		);

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_PERSONAL_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()->__toString()
		);
	}

	public function testWhenQueriedByMultipleUuids_andGivenValidCurrentUuid_addressChangeIsReturned(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create(
			AddressChangeId::fromString( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION ),
			$this->newPersonalAddress()
		)->forPerson()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChangeRepository->storeAddressChange( $addressChange );

		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuids(
			$addressChange->getCurrentIdentifier()->__toString(),
			self::INVALID_UPDATE_TOKEN
		);

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_PERSONAL_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()->__toString()
		);
	}

	public function testWhenQueriedByMultipleUuids_andGivenInvalidDonationUuids_nullIsReturned(): void {
		$addressChange = ( new DoctrineAddressChangeRepository( $this->em ) )->getAddressChangeByUuids(
			self::INVALID_UPDATE_TOKEN,
			self::INVALID_UPDATE_TOKEN
		);

		$this->assertNull( $addressChange );
	}

	private function storeAddressChange( string $uuid, bool $isPersonal = true ): void {
		if ( $isPersonal ) {
			$addressChange = AddressChangeBuilder::create(
				AddressChangeId::fromString( $uuid ),
				$this->newPersonalAddress()
			)
				->forPerson()
				->forDonation( self::DUMMY_DONATION_ID )
				->build();
		} else {
			$addressChange = AddressChangeBuilder::create(
				AddressChangeId::fromString( $uuid ),
				$this->newCompanyAddress()
			)
				->forCompany()
				->forDonation( self::DUMMY_DONATION_ID )
				->build();
		}
		$this->em->persist( $addressChange );
		$this->em->flush();
		$this->em->clear();
	}

	private function retrieveAddressChangeByUuid( string $uuid ): ?AddressChange {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		return $addressChangeRepository->getAddressChangeByUuid(
			$uuid
		);
	}

	private function newPersonalAddress(): Address {
		return Address::newPersonalAddress(
			'Herr',
			'Prof. Dr.',
			'Test',
			'User',
			'Teststreet 12345',
			'98765',
			'Berlin',
			'Germany'
		);
	}

	private function newCompanyAddress(): Address {
		return Address::newCompanyAddress(
			'Test Company',
			'Teststreet 123',
			'324324',
			'Not Berlin',
			'Somewhere'
		);
	}

	private function assertDatePropertyIsSet( \DateTimeInterface $expectedDate, AddressChange $addressChange, string $propertyName, float $delta = 1.0 ): void {
		// We're peeking into private properties to make sure the dates, which are not exposed through getters at the domain level,
		// are properly written at the DB level
		$dateField = new \ReflectionProperty( AddressChange::class, $propertyName );
		$dateField->setAccessible( true );
		$actualDate = $dateField->getValue( $addressChange );
		$this->assertInstanceOf( \DateTimeInterface::class, $actualDate );
		$this->assertEqualsWithDelta( $actualDate->getTimestamp(), $expectedDate->getTimestamp(), $delta, 'Dates do not match.' );
	}
}
