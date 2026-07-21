<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Integration\DataAccess;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WMDE\Clock\StubClock;
use WMDE\Clock\SystemClock;
use WMDE\Fundraising\AddressChangeContext\DataAccess\DoctrineAddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\DataAccess\DoctrineAddressDeleter;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\Address;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;
use WMDE\Fundraising\AddressChangeContext\Tests\TestEnvironment;

#[CoversClass( DoctrineAddressDeleter::class )]
class DoctrineAddressDeleterTest extends TestCase {

	private const string UPDATE_TOKEN = '2a54c0a1-fc94-4ef8-8b0a-7c2ed8565521';
	private const int DONATION_ID = 1;
	private EntityManager $em;
	private DoctrineAddressChangeRepository $repository;

	public function setUp(): void {
		$this->em = TestEnvironment::newInstance()->getEntityManager();
		$this->repository = new DoctrineAddressChangeRepository( $this->em );
		parent::setUp();
	}

	public function testDeleteAllDeletesExportedAddresses(): void {
		$addressChange = $this->newAddressChange();
		$addressChange->markAsExported();
		$this->repository->storeAddressChange( $addressChange );

		$deleter = new DoctrineAddressDeleter( $this->em, new SystemClock(), new \DateInterval( 'P1M' ) );

		$deleter->deleteAll();

		$count = $this->em->getConnection()->executeQuery( 'SELECT COUNT(*) FROM address' )->fetchOne();

		$this->assertSame( 0, $count );
	}

	public function testDeleteAllDeletesOldAddresses(): void {
		$addressChange = $this->newAddressChange();
		$this->repository->storeAddressChange( $addressChange );

		// Set the time period to the same as the grace period
		$currentTime = ( new \DateTimeImmutable() )->add( new \DateInterval( 'P1M' ) );

		$deleter = new DoctrineAddressDeleter( $this->em, new StubClock( $currentTime ), new \DateInterval( 'P1M' ) );

		$deleter->deleteAll();

		$count = $this->em->getConnection()->executeQuery( 'SELECT COUNT(*) FROM address' )->fetchOne();

		$this->assertSame( 0, $count );
	}

	public function testDeleteAllDoesNotDeleteNewUnexportedAddresses(): void {
		$addressChange = $this->newAddressChange();
		$this->repository->storeAddressChange( $addressChange );

		// Set the time period 1 day less than the grace period
		$currentTime = ( new \DateTimeImmutable() )->add( new \DateInterval( 'P1M' ) );
		$currentTime = $currentTime->sub( new \DateInterval( 'P1D' ) );

		$deleter = new DoctrineAddressDeleter( $this->em, new StubClock( $currentTime ), new \DateInterval( 'P1M' ) );

		$deleter->deleteAll();

		$count = $this->em->getConnection()->executeQuery( 'SELECT COUNT(*) FROM address' )->fetchOne();

		$this->assertSame( 1, $count );
	}

	public function testDeleteAllUpdatesAddressChanges(): void {
		$addressChange = $this->newAddressChange();
		$addressChange->markAsExported();
		$this->repository->storeAddressChange( $addressChange );

		$deleter = new DoctrineAddressDeleter( $this->em, new SystemClock(), new \DateInterval( 'P1M' ) );

		$deleter->deleteAll();

		$addressChange = $this->em->getConnection()->executeQuery( 'SELECT * FROM address_change' )->fetchAssociative();

		$this->assertIsArray( $addressChange, 'Database query should return an array, check for errors' );
		$this->assertNull( $addressChange[ 'export_date' ] );
		$this->assertNull( $addressChange[ 'address_id' ] );
	}

	private function newAddressChange(): AddressChange {
		return AddressChangeBuilder::create(
			AddressChangeId::fromString( self::UPDATE_TOKEN ),
			Address::newPersonalAddress(
				'Herr',
				'Prof. Dr.',
				'Test',
				'User',
				'Teststreet 12345',
				'98765',
				'Berlin',
				'Germany'
			)
		)
			->forPerson()
			->forDonation( self::DONATION_ID )
			->build();
	}

}
