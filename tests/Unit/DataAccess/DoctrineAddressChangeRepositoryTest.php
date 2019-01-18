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

	const VALID_UPDATE_TOKEN_PERSONAL_DONATION = '1ba905fe68e61f3a681d8faf689bfeeb8c942123';
	const VALID_UPDATE_TOKEN_PERSONAL_MEMBERSHIP = '2ba905fe68e61f3a681d8faf689bfeeb8c942456';
	const VALID_UPDATE_TOKEN_COMPANY_DONATION = '3ba905fe68e61f3a681d8faf689bfeeb8c942789';
	const VALID_UPDATE_TOKEN_COMPANY_MEMBERSHIP = '4ba905fe68e61f3a681d8faf689bfeeb8c942666';
	const INVALID_UPDATE_TOKEN = 'ThisTokenIsLikeTotallyNotValid1234567890';

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
