<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress;

use WMDE\Fundraising\AddressChangeContext\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\Address;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;

class ChangeAddressUseCase {

	private AddressChangeRepository $addressChangeRepository;

	public function __construct( AddressChangeRepository $addressChangeRepository ) {
		$this->addressChangeRepository = $addressChangeRepository;
	}

	public function changeAddress( ChangeAddressRequest $request ): ChangeAddressResponse {
		$addressChange = $this->addressChangeRepository->getAddressChangeByUuid( $request->identifier );
		if ( $addressChange === null ) {
			return ChangeAddressResponse::newErrorResponse( [ ChangeAddressResponse::ERROR_ADDRESS_NOT_FOUND ] );
		}

		$newIdentifier = AddressChangeId::fromString( AddressChangeBuilder::generateUuid() );
		if ( $request->hasAddressChangeData() ) {
			try {
				$addressChange->performAddressChange( $this->buildAddress( $request ), $newIdentifier );
			} catch ( ChangeAddressValidationException $e ) {
				return ChangeAddressResponse::newErrorResponse( [ $e->getMessage() ] );
			}
		}

		if ( $request->isOptedOutOfDonationReceipt() ) {
			$addressChange->optOutOfDonationReceipt( $newIdentifier );
		}

		$this->addressChangeRepository->storeAddressChange( $addressChange );
		return ChangeAddressResponse::newSuccessResponse();
	}

	private function buildAddress( ChangeAddressRequest $request ): Address {
		if ( $request->isPersonal() ) {
			return Address::newPersonalAddress(
				$request->salutation,
				$request->title,
				$request->firstName,
				$request->lastName,
				$request->address,
				$request->postcode,
				$request->city,
				$request->country
			);
		} elseif ( $request->isCompany() ) {
			return Address::newCompanyAddress(
				$request->company,
				$request->address,
				$request->postcode,
				$request->city,
				$request->country
			);
		}
		throw new ChangeAddressValidationException( 'Address Type' );
	}

}
