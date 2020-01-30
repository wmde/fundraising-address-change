<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\UseCases\ChangeAddress;

use WMDE\Fundraising\AddressChange\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChange\Domain\Model\Address;

class ChangeAddressUseCase {

	private $addressChangeRepository;

	public function __construct( AddressChangeRepository $addressChangeRepository ) {
		$this->addressChangeRepository = $addressChangeRepository;
	}

	public function changeAddress( ChangeAddressRequest $request ): ChangeAddressResponse {
		$addressChange = $this->addressChangeRepository->getAddressChangeByUuid( $request->getIdentifier() );
		if ( $addressChange === null ) {
			return ChangeAddressResponse::newErrorResponse( ['Unknown address.'] );
		}

		if ( $request->hasAddressChangeData() ) {
			try {
				$addressChange->performAddressChange( $this->buildAddress( $request ) );
			}
			catch ( ChangeAddressValidationException $e ) {
				return ChangeAddressResponse::newErrorResponse( [ $e->getMessage() ] );
			}
		}

		if ( $request->isOptedOutOfDonationReceipt() ) {
			$addressChange->optOutOfDonationReceipt();
		}

		$this->addressChangeRepository->storeAddressChange( $addressChange );
		return ChangeAddressResponse::newSuccessResponse();
	}

	private function buildAddress( ChangeAddressRequest $request ): Address {
		if ( $request->isPersonal() ) {
			return Address::newPersonalAddress(
				$request->getSalutation(),
				$request->getTitle(),
				$request->getFirstName(),
				$request->getLastName(),
				$request->getAddress(),
				$request->getPostcode(),
				$request->getCity(),
				$request->getCountry()
			);
		} elseif ( $request->isCompany() ) {
			return Address::newCompanyAddress(
				$request->getCompany(),
				$request->getAddress(),
				$request->getPostcode(),
				$request->getCity(),
				$request->getCountry()
			);
		}
		throw new ChangeAddressValidationException( 'Address Type' );
	}

}