<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\UseCases;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressType;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressRequest;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressResponse;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressUseCase;

/**
 * @covers \WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressUseCase
 * @covers \WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressResponse
 */
class ChangeAddressUseCaseTest extends TestCase {

	private const VALID_SAMPLE_UUID = 'd8441c6e-1f7a-4710-97d3-0e2126c86d40';
	private const DUMMY_DONATION_ID = 0;

	public function testGivenValidAddressChangeRequest_successResponseIsReturned(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuid' )->willReturn(
			$this->createAddressChange()
		);
		$useCase = new ChangeAddressUseCase( $mockAddressChangeRepository );
		$response = $useCase->changeAddress( $this->newChangeAddressRequest() );
		$this->assertTrue( $response->isSuccess() );
	}

	public function testGivenInvalidAddressChangeRequest_errorResponseIsReturned(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuid' )->willReturn(
			$this->createAddressChange()
		);
		$useCase = new ChangeAddressUseCase( $mockAddressChangeRepository );
		$response = $useCase->changeAddress( $this->newMissingDataChangeAddressRequest() );
		$this->assertFalse( $response->isSuccess() );
	}

	public function testGivenAddressChangeRequestIdentifierCannotBeFound_errorResponseIsReturned(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuid' )->willReturn(
			null
		);
		$useCase = new ChangeAddressUseCase( $mockAddressChangeRepository );
		$response = $useCase->changeAddress( $this->newChangeAddressRequest() );
		$this->assertFalse( $response->isSuccess() );
		$this->assertEquals( [ ChangeAddressResponse::ERROR_ADDRESS_NOT_FOUND ], $response->getErrors() );
	}

	public function testGivenValidOptOutOnlyChangeRequest_successResponseIsReturned(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuid' )->willReturn(
			$this->createAddressChange()
		);
		$useCase = new ChangeAddressUseCase( $mockAddressChangeRepository );
		$response = $useCase->changeAddress( $this->newOptOutOnlyRequest() );
		$this->assertTrue( $response->isSuccess() );
	}

	public function testGivenEmptyAddressChangeRequest_errorResponseIsReturned(): void {
		$mockAddressChangeRepository = $this->createMock( AddressChangeRepository::class );
		$mockAddressChangeRepository->method( 'getAddressChangeByUuid' )->willReturn(
			$this->createAddressChange()
		);
		$useCase = new ChangeAddressUseCase( $mockAddressChangeRepository );
		$response = $useCase->changeAddress( $this->newEmptyChangeAddressRequest() );
		$this->assertFalse( $response->isSuccess() );
	}

	private function newChangeAddressRequest(): ChangeAddressRequest {
		return ChangeAddressRequest::newPersonalChangeAddressRequest(
			salutation: 'Herr',
			title: 'Prof. Dr.',
			firstName: 'Test Name',
			lastName: 'Test Last Name',
			address: 'Test',
			postcode: '12345',
			city: 'Test City',
			country: 'Test Country',
			identifier: self::VALID_SAMPLE_UUID,
			donationReceipt: true,
			isOptOutOnly: false,
		);
	}

	private function newMissingDataChangeAddressRequest(): ChangeAddressRequest {
		return ChangeAddressRequest::newPersonalChangeAddressRequest(
			salutation: 'Herr',
			title: 'Prof. Dr.',
			firstName: 'Test Name',
			lastName: 'Test Last Name',
			address: 'Test',
			postcode: '12345',
			city: '',
			country: 'Test Country',
			identifier: self::VALID_SAMPLE_UUID,
			donationReceipt: false,
			isOptOutOnly: false,
		);
	}

	private function newEmptyChangeAddressRequest(): ChangeAddressRequest {
		return ChangeAddressRequest::newPersonalChangeAddressRequest(
			salutation: '',
			title: '',
			firstName: '',
			lastName: '',
			address: '',
			postcode: '',
			city: '',
			country: '',
			identifier: self::VALID_SAMPLE_UUID,
			donationReceipt: true,
			isOptOutOnly: false,
		);
	}

	private function newOptOutOnlyRequest(): ChangeAddressRequest {
		return ChangeAddressRequest::newPersonalChangeAddressRequest(
			salutation: '',
			title: '',
			firstName: '',
			lastName: '',
			address: '',
			postcode: '',
			city: '',
			country: '',
			identifier: self::VALID_SAMPLE_UUID,
			donationReceipt: true,
			isOptOutOnly: true,
		);
	}

	private function createAddressChange(): AddressChange {
		return new AddressChange(
			AddressType::Person,
			AddressChange::EXTERNAL_ID_TYPE_DONATION,
			self::DUMMY_DONATION_ID,
			AddressChangeId::fromString( self::VALID_SAMPLE_UUID )
		);
	}

}
