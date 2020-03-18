<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\DataAccess;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChange\DataAccess\DoctrineAddressChangeRepository;
use WMDE\Fundraising\AddressChange\Domain\Model\Address;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\AddressChangeContext\Tests\TestEnvironment;

/**
 * @covers \WMDE\Fundraising\AddressChange\DataAccess\DoctrineAddressChangeRepository
 */
class DoctrineAddressChangeRepositoryTest extends TestCase {

	const VALID_UPDATE_TOKEN_PERSONAL_DONATION = '2a54c0a1-fc94-4ef8-8b0a-7c2ed8565521';
	const VALID_UPDATE_TOKEN_PERSONAL_MEMBERSHIP = 'ce4449f9-8317-41fa-acc3-4a878e26845d';
	const VALID_UPDATE_TOKEN_COMPANY_DONATION = 'c52258ba-fed1-476a-a7e5-c721df087c12';
	const VALID_UPDATE_TOKEN_COMPANY_MEMBERSHIP = '8d11d2ba-5ec5-4ec8-a08c-0ac7b8654b59';
	const INVALID_UPDATE_TOKEN = '72dfed91-fa40-4af0-9e80-c6010ab29cd1';
	private const DUMMY_DONATION_ID = 0;

	/** @var EntityManager */
	private $em;

	public function setUp(): void {
		$this->em = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	public function testGivenValidPersonalDonationUuid_addressChangeIsReturned(): void {
		$this->storeAddressChange( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION, true );
		$retrievedAddressChange = $this->retrieveAddressChangeByUuid( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION );

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_PERSONAL_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()
		);
		$this->assertTrue( $retrievedAddressChange->isPersonalAddress() );
	}

	public function testGivenValidCompanyDonationUuid_addressChangeIsReturned(): void {
		$this->storeAddressChange( self::VALID_UPDATE_TOKEN_COMPANY_DONATION, false );
		$retrievedAddressChange = $this->retrieveAddressChangeByUuid( self::VALID_UPDATE_TOKEN_COMPANY_DONATION );

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_COMPANY_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()
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

		$this->em->clear( AddressChange::class );
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( $addressChange->getCurrentIdentifier() );
		$this->assertNotNull( $retrievedAddressChange );
		$this->assertNotNull( $retrievedAddressChange->getAddress() );
		$this->assertNotNull( $addressChange->getAddress() ); // avoid PHPStan errors when accessing address later
		$this->assertTrue( $retrievedAddressChange->isPersonalAddress() );
		$this->assertSame( $donationId, $retrievedAddressChange->getExternalId() );
		$this->assertSame( AddressChange::EXTERNAL_ID_TYPE_DONATION, $retrievedAddressChange->getExternalIdType() );
		$this->assertFalse( $retrievedAddressChange->isExported() );
		$this->assertDatePropertyIsSet( $now, $retrievedAddressChange, 'createdAt' );
		$this->assertDatePropertyIsSet( $now, $retrievedAddressChange, 'modifiedAt' );
		$this->assertSame( $addressChange->getCurrentIdentifier(), $retrievedAddressChange->getCurrentIdentifier() );
		$this->assertSame( $addressChange->getAddress()->isPersonalAddress(), $retrievedAddressChange->getAddress()->isPersonalAddress() );
	}

	public function testGivenAddressChangeWithReceiptOptIn_optInIsStoredCorrectly(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create()->forCompany()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChangeRepository->storeAddressChange( $addressChange );

		$this->em->clear( AddressChange::class );
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( $addressChange->getCurrentIdentifier() );
		$this->assertTrue( $retrievedAddressChange->isOptedIntoDonationReceipt() );
	}

	public function testGivenAddressChangeWithReceiptOptOut_optOutIsStoredCorrectly(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create()->forCompany()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChange->optOutOfDonationReceipt();
		$addressChangeRepository->storeAddressChange( $addressChange );

		$this->em->clear( AddressChange::class );
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( $addressChange->getCurrentIdentifier() );
		$this->assertFalse( $retrievedAddressChange->isOptedIntoDonationReceipt() );
	}

	public function testGivenExportedAddressChange_itsStateIsStoredCorrectly(): void {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = AddressChangeBuilder::create( null, $this->newPersonalAddress() )->forPerson()->forDonation( self::DUMMY_DONATION_ID )->build();
		$addressChange->markAsExported();
		$addressChangeRepository->storeAddressChange( $addressChange );
		$now = new \DateTime();

		$this->em->clear( AddressChange::class );
		$retrievedAddressChange = $addressChangeRepository->getAddressChangeByUuid( $addressChange->getCurrentIdentifier() );
		$this->assertNotNull( $retrievedAddressChange );
		$this->assertTrue( $retrievedAddressChange->isExported() );
		$this->assertDatePropertyIsSet( $now, $retrievedAddressChange, 'exportDate' );
	}

	private function storeAddressChange( string $uuid, bool $isPersonal = true ): void {
		if ( $isPersonal ) {
			$addressChange = AddressChangeBuilder::create(
				$uuid,
				$this->newPersonalAddress()
			)
				->forPerson()
				->forDonation( self::DUMMY_DONATION_ID )
				->build();
		} else {
			$addressChange = AddressChangeBuilder::create(
				$uuid,
				$this->newCompanyAddress()
			)
				->forCompany()
				->forDonation( self::DUMMY_DONATION_ID )
				->build();
		}
		$this->em->persist( $addressChange );
		$this->em->flush();
		$this->em->clear( AddressChange::class );
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

	private function assertDatePropertyIsSet( \DateTime $expectedDate, AddressChange $addressChange, string $propertyName, float $delta = 1.0 ): void {
		// We're peeking into private properties to make sure the dates, which are not exposed through getters at the domain level,
		// are properly written at the DB level
		$dateField = new \ReflectionProperty( AddressChange::class, $propertyName );
		$dateField->setAccessible( true );
		$actualDate = $dateField->getValue( $addressChange );
		$this->assertEqualsWithDelta( $actualDate->getTimestamp(), $expectedDate->getTimestamp(), $delta, 'Dates do not match.' );
	}
}
