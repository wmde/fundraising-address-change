<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\UseCases;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressRequest;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressUseCase;

/**
 * @covers \WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressUseCase
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
		$request = new ChangeAddressRequest();
		$request->setIdentifier( self::VALID_SAMPLE_UUID );
		$request->setAddress( 'Test' );
		$request->setAddressType( AddressChange::ADDRESS_TYPE_PERSON );
		$request->setCity( 'Test City' );
		$request->setCountry( 'Test Country' );
		$request->setFirstName( 'Test Name' );
		$request->setLastName( 'Test Last Name' );
		$request->setPostcode( '12345' );
		$request->setSalutation( 'Herr' );
		$request->setTitle( 'Prof. Dr.' );
		$request->setDonationReceipt( true );
		$request->setIsOptOutOnly( false );
		$request->freeze();
		return $request;
	}

	private function newMissingDataChangeAddressRequest(): ChangeAddressRequest {
		$request = new ChangeAddressRequest();
		$request->setIdentifier( self::VALID_SAMPLE_UUID );
		$request->setAddress( 'Test' );
		$request->setAddressType( AddressChange::ADDRESS_TYPE_PERSON );
		$request->setCity( '' );
		$request->setCountry( 'Test Country' );
		$request->setFirstName( 'Test Name' );
		$request->setLastName( 'Test Last Name' );
		$request->setPostcode( '12345' );
		$request->setSalutation( 'Herr' );
		$request->setTitle( 'Prof. Dr.' );
		$request->setDonationReceipt( false );
		$request->setIsOptOutOnly( false );
		$request->freeze();
		return $request;
	}

	private function newEmptyChangeAddressRequest(): ChangeAddressRequest {
		$request = new ChangeAddressRequest();
		$request->setIdentifier( self::VALID_SAMPLE_UUID );
		$request->setAddress( '' );
		$request->setAddressType( AddressChange::ADDRESS_TYPE_PERSON );
		$request->setCity( '' );
		$request->setCountry( '' );
		$request->setFirstName( '' );
		$request->setLastName( '' );
		$request->setPostcode( '' );
		$request->setSalutation( '' );
		$request->setTitle( '' );
		$request->setDonationReceipt( true );
		$request->setIsOptOutOnly( false );
		$request->freeze();
		return $request;
	}

	private function newOptOutOnlyRequest(): ChangeAddressRequest {
		$request = new ChangeAddressRequest();
		$request->setIdentifier( self::VALID_SAMPLE_UUID );
		$request->setAddress( '' );
		$request->setAddressType( AddressChange::ADDRESS_TYPE_PERSON );
		$request->setCity( '' );
		$request->setCountry( '' );
		$request->setFirstName( '' );
		$request->setLastName( '' );
		$request->setPostcode( '' );
		$request->setSalutation( '' );
		$request->setTitle( '' );
		$request->setDonationReceipt( false );
		$request->setIsOptOutOnly( true );
		$request->freeze();
		return $request;
	}

	private function createAddressChange(): AddressChange {
		return new AddressChange( AddressChange::ADDRESS_TYPE_PERSON, AddressChange::EXTERNAL_ID_TYPE_DONATION, self::DUMMY_DONATION_ID, self::VALID_SAMPLE_UUID );
	}

}
