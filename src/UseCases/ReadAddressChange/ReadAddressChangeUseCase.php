<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange;

use WMDE\Fundraising\AddressChangeContext\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;

class ReadAddressChangeUseCase {

	private AddressChangeRepository $addressChangeRepository;

	public function __construct( AddressChangeRepository $addressChangeRepository ) {
		$this->addressChangeRepository = $addressChangeRepository;
	}

	public function getAddressChangeByUuids( string $currentIdentifier, string $previousIdentifier ): ?AddressChangeData {
		$addressChange = $this->addressChangeRepository->getAddressChangeByUuids( $currentIdentifier, $previousIdentifier );

		if ( !$addressChange ) {
			return null;
		}

		return new AddressChangeData(
			$addressChange->getCurrentIdentifier()->__toString(),
			$addressChange->getPreviousIdentifier()->__toString(),
			$this->getAddressData( $addressChange ),
			$addressChange->isOptedIntoDonationReceipt(),
			$addressChange->getExportState(),
		);
	}

	/**
	 * @param AddressChange $addressChange
	 *
	 * @return array<string, mixed>
	 */
	private function getAddressData( AddressChange $addressChange ): array {
		if ( !$addressChange->getAddress() ) {
			return [];
		}

		return [
			'salutation' => $addressChange->getAddress()->getSalutation(),
			'company' => $addressChange->getAddress()->getCompany(),
			'title' => $addressChange->getAddress()->getTitle(),
			'firstName' => $addressChange->getAddress()->getFirstName(),
			'lastName' => $addressChange->getAddress()->getLastName(),
			'street' => $addressChange->getAddress()->getAddress(),
			'postcode' => $addressChange->getAddress()->getPostcode(),
			'city' => $addressChange->getAddress()->getCity(),
			'country' => $addressChange->getAddress()->getCountry(),
			'isPersonalAddress' => $addressChange->isPersonalAddress(),
			'isCompanyAddress' => $addressChange->isCompanyAddress(),
		];
	}
}
