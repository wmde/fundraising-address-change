<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\DataAccess;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChange\DataAccess\DoctrineAddressChangeRepository;
use WMDE\Fundraising\AddressChange\Domain\Model\Address;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;
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

	/** @var EntityManager */
	private $em;

	public function setUp(): void {
		$this->em = TestEnvironment::newInstance()->getFactory()->getEntityManager();
		parent::setUp();
	}

	public function testGivenValidPersonalDonationUuid_addressChangeIsReturned() {
		$this->storeAddressChange( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION, true );
		$retrievedAddressChange = $this->retrieveAddressChangeByUuid( self::VALID_UPDATE_TOKEN_PERSONAL_DONATION );

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_PERSONAL_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()
		);
		$this->assertTrue( $retrievedAddressChange->isPersonalAddress() );
	}

	public function testGivenValidCompanyDonationUuid_addressChangeIsReturned() {
		$this->storeAddressChange( self::VALID_UPDATE_TOKEN_COMPANY_DONATION, false );
		$retrievedAddressChange = $this->retrieveAddressChangeByUuid( self::VALID_UPDATE_TOKEN_COMPANY_DONATION );

		$this->assertNotNull( $retrievedAddressChange );
		$this->assertSame(
			self::VALID_UPDATE_TOKEN_COMPANY_DONATION,
			$retrievedAddressChange->getCurrentIdentifier()
		);
		$this->assertTrue( $retrievedAddressChange->isCompanyAddress() );
	}

	public function testGivenInvalidDonationUuid_nullIsReturned() {
		$addressChangeRepository = new DoctrineAddressChangeRepository( $this->em );
		$addressChange = $addressChangeRepository->getAddressChangeByUuid( self::INVALID_UPDATE_TOKEN );
		$this->assertNull( $addressChange );
	}

	private function storeAddressChange( string $uuid, bool $isPersonal = true ): void {
		if ( $isPersonal ) {
			$addressChange = AddressChange::createNewPersonAddressChange(
				$uuid,
				$this->newPersonalAddress()
			);
		} else {
			$addressChange = AddressChange::createNewCompanyAddressChange(
				$uuid,
				$this->newCompanyAddress()
			);
		}
		$this->em->persist( $addressChange );
		$this->em->flush();
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
}
