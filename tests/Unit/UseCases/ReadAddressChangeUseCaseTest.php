<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\UseCases;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\Address;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;
use WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange\ReadAddressChangeUseCase;

/**
 * @covers \WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange\ReadAddressChangeUseCase
 * @covers \WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange\AddressChangeData
 */
class ReadAddressChangeUseCaseTest extends TestCase {

	private const VALID_UUID = '2a54c0a1-fc94-4ef8-8b0a-7c2ed8565521';
	private const UPDATE_UUID = 'c52258ba-fed1-476a-a7e5-c721df087c12';
	private const INVALID_UUID = 'NOT A VALID UUID';
	private const DUMMY_DONATION_ID = 0;

	public function testGivenValidUuid_returnsAddressChangeData(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuids' )->willReturn(
			$this->createUsedAddressChange()
		);

		$readAddressUseCase = new ReadAddressChangeUseCase( $mockAddressChangeRepository );
		$expectedAddress = [
			'salutation' => 'Herr',
			'company' => '',
			'title' => 'Prof. Dr.',
			'firstName' => 'Test',
			'lastName' => 'User',
			'street' => 'Teststreet 12345',
			'postcode' => '98765',
			'city' => 'Berlin',
			'country' => 'Germany',
			'isPersonalAddress' => true,
			'isCompanyAddress' => false,
		];

		$addressChangeData = $readAddressUseCase->getAddressChangeByUuids( self::VALID_UUID, self::VALID_UUID );

		$this->assertEquals( self::UPDATE_UUID, $addressChangeData->identifier );
		$this->assertEquals( self::VALID_UUID, $addressChangeData->previousIdentifier );
		$this->assertEquals( $expectedAddress, $addressChangeData->address );
		$this->assertTrue( $addressChangeData->donationReceipt );
		$this->assertEquals( AddressChange::EXPORT_STATE_USED_NOT_EXPORTED, $addressChangeData->exportState );
	}

	public function testGivenValidUuidForUnusedAddressChange_returnsAddressChangeData(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuids' )->willReturn(
			$this->createUnusedAddressChange()
		);

		$readAddressUseCase = new ReadAddressChangeUseCase( $mockAddressChangeRepository );

		$addressChangeData = $readAddressUseCase->getAddressChangeByUuids( self::VALID_UUID, self::VALID_UUID );

		$this->assertEquals( self::VALID_UUID, $addressChangeData->identifier );
		$this->assertEquals( self::VALID_UUID, $addressChangeData->previousIdentifier );
		$this->assertEquals( [], $addressChangeData->address );
		$this->assertTrue( $addressChangeData->donationReceipt );
		$this->assertEquals( AddressChange::EXPORT_STATE_NO_DATA, $addressChangeData->exportState );
	}

	public function testGivenInvalidDonationUuids_returnsNull(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuids' )->willReturn( null );

		$readAddressUseCase = new ReadAddressChangeUseCase( $mockAddressChangeRepository );

		$this->assertNull( $readAddressUseCase->getAddressChangeByUuids( self::INVALID_UUID, self::INVALID_UUID ) );
	}

	private function createUnusedAddressChange(): AddressChange {
		return new AddressChange(
			AddressChange::ADDRESS_TYPE_PERSON,
			AddressChange::EXTERNAL_ID_TYPE_DONATION,
			self::DUMMY_DONATION_ID,
			AddressChangeId::fromString( self::VALID_UUID )
		);
	}

	private function createUsedAddressChange(): AddressChange {
		$addressChange = new AddressChange(
			AddressChange::ADDRESS_TYPE_PERSON,
			AddressChange::EXTERNAL_ID_TYPE_DONATION,
			self::DUMMY_DONATION_ID,
			AddressChangeId::fromString( self::VALID_UUID )
		);

		$addressChange->performAddressChange(
			$this->newPersonalAddress(),
			AddressChangeId::fromString( self::UPDATE_UUID )
		);

		return $addressChange;
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
}
