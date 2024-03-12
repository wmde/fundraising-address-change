<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress;

use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressType;

class ChangeAddressRequest {
	private function __construct(
		public readonly AddressType $addressType,
		public readonly string $address,
		public readonly string $postcode,
		public readonly string $city,
		public readonly string $country,
		public readonly string $identifier,
		public readonly bool $donationReceipt,
		public readonly bool $isOptOutOnly,
		public readonly string $company = '',
		public readonly string $salutation = '',
		public readonly string $title = '',
		public readonly string $firstName = '',
		public readonly string $lastName = '',
	) {
	}

	public static function newPersonalChangeAddressRequest(
		string $salutation,
		string $title,
		string $firstName,
		string $lastName,
		string $address,
		string $postcode,
		string $city,
		string $country,
		string $identifier,
		bool $donationReceipt,
		bool $isOptOutOnly,
	): ChangeAddressRequest {
		return new self(
			addressType: AddressType::Person,
			address: trim( $address ),
			postcode: trim( $postcode ),
			city: trim( $city ),
			country: trim( $country ),
			identifier: trim( $identifier ),
			donationReceipt: $donationReceipt,
			isOptOutOnly: $isOptOutOnly,
			salutation: trim( $salutation ),
			title: trim( $title ),
			firstName: trim( $firstName ),
			lastName: trim( $lastName ),
		);
	}

	public static function newCompanyChangeAddressRequest(
		string $company,
		string $address,
		string $postcode,
		string $city,
		string $country,
		string $identifier,
		bool $donationReceipt,
		bool $isOptOutOnly,
	): ChangeAddressRequest {
		return new self(
			addressType: AddressType::Company,
			address: trim( $address ),
			postcode: trim( $postcode ),
			city: trim( $city ),
			country: trim( $country ),
			identifier: trim( $identifier ),
			donationReceipt: $donationReceipt,
			isOptOutOnly: $isOptOutOnly,
			company: trim( $company ),
		);
	}

	public function isPersonal(): bool {
		return $this->addressType === AddressType::Person;
	}

	public function isCompany(): bool {
		return $this->addressType === AddressType::Company;
	}

	public function isOptedOutOfDonationReceipt(): bool {
		return !$this->donationReceipt;
	}

	public function hasAddressChangeData(): bool {
		return !$this->isOptOutOnly;
	}

}
