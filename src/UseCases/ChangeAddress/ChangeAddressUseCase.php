<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\UseCases\ChangeAddress;

use WMDE\Fundraising\AddressChange\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;

class ChangeAddressUseCase {

	private $addressChangeRepository;

	public function __construct( AddressChangeRepository $addressChangeRepository ) {
		$this->addressChangeRepository = $addressChangeRepository;
	}

	public function changeAddress( ChangeAddressRequest $request ): ChangeAddressResponse {
		try {
			$addressChange = $this->buildAddressChange( $request );
		} catch ( ChangeAddressException $e ) {
			return ChangeAddressResponse::newErrorResponse( [ $e->getMessage() ] );
		}
		$this->addressChangeRepository->storeAddressChange( $addressChange );
		return ChangeAddressResponse::newSuccessResponse();
	}

	private function buildAddressChange( ChangeAddressRequest $request ): AddressChange {
		throw new ChangeAddressException( 'Not implemented yet' );

		// TODO Validate address data in ChangeAddressRequest (name or company must be non-empty, postal adresss fields must be filled)
		// TODO create Address class
		// TODO create AddressChange class
	}

}