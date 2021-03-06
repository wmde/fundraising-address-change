<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress;

use WMDE\FreezableValueObject\FreezableValueObject;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;

class ChangeAddressRequest {
	use FreezableValueObject;

	private string $salutation;

	private string $company;

	private string $title;

	private string $firstName;

	private string $lastName;

	private string $address;

	private string $postcode;

	private string $city;

	private string $country;

	private string $addressType;

	private string $identifier;

	private bool $donationReceipt;

	private bool $isOptOutOnly;

	public function getSalutation(): string {
		return $this->salutation;
	}

	public function setSalutation( string $salutation ): self {
		$this->assertIsWritable();
		$this->salutation = $salutation;
		return $this;
	}

	public function getCompany(): string {
		return $this->company;
	}

	public function setCompany( string $company ): self {
		$this->assertIsWritable();
		$this->company = $company;
		return $this;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle( string $title ): self {
		$this->assertIsWritable();
		$this->title = $title;
		return $this;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function setFirstName( string $firstName ): self {
		$this->assertIsWritable();
		$this->firstName = $firstName;
		return $this;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function setLastName( string $lastName ): self {
		$this->assertIsWritable();
		$this->lastName = $lastName;
		return $this;
	}

	public function getAddress(): string {
		return $this->address;
	}

	public function setAddress( string $address ): self {
		$this->assertIsWritable();
		$this->address = $address;
		return $this;
	}

	public function getPostcode(): string {
		return $this->postcode;
	}

	public function setPostcode( string $postcode ): self {
		$this->assertIsWritable();
		$this->postcode = $postcode;
		return $this;
	}

	public function getCity(): string {
		return $this->city;
	}

	public function setCity( string $city ): self {
		$this->assertIsWritable();
		$this->city = $city;
		return $this;
	}

	public function getCountry(): string {
		return $this->country;
	}

	public function setCountry( string $country ): self {
		$this->assertIsWritable();
		$this->country = $country;
		return $this;
	}

	public function getAddressType(): string {
		return $this->addressType;
	}

	public function isPersonal(): bool {
		return $this->addressType === AddressChange::ADDRESS_TYPE_PERSON;
	}

	public function isCompany(): bool {
		return $this->addressType === AddressChange::ADDRESS_TYPE_COMPANY;
	}

	public function setAddressType( string $addressType ): self {
		$this->assertIsWritable();
		$this->addressType = $addressType;
		return $this;
	}

	public function getIdentifier(): string {
		return $this->identifier;
	}

	public function setIdentifier( string $identifier ): self {
		$this->assertIsWritable();
		$this->identifier = $identifier;
		return $this;
	}

	public function isOptedOutOfDonationReceipt(): bool {
		return !$this->donationReceipt;
	}

	public function setDonationReceipt( bool $donationReceipt ): self {
		$this->assertIsWritable();
		$this->donationReceipt = $donationReceipt;
		return $this;
	}

	public function setIsOptOutOnly( bool $isOptOutOnly ): self {
		$this->assertIsWritable();
		$this->isOptOutOnly = $isOptOutOnly;
		return $this;
	}

	public function hasAddressChangeData(): bool {
		return !$this->isOptOutOnly;
	}

}
