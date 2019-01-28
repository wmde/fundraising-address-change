<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\UseCases\ChangeAddress;

use WMDE\FreezableValueObject\FreezableValueObject;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;

class ChangeAddressRequest {
	use FreezableValueObject;

	private $salutation;

	private $company;

	private $title;

	private $firstName;

	private $lastName;

	private $address;

	private $postcode;

	private $city;

	private $country;

	private $addressType;

	private $identifier;

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

}