<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressValidationException;

class Address {

	private const TYPE_PERSONAL = 'personal';

	private const TYPE_COMPANY = 'company';

	/**
	 * @var int|null
	 */
	private $id;

	private $salutation;

	private $company;

	private $title;

	private $firstName = '';

	private $lastName = '';

	private $address;

	private $postcode;

	private $city;

	private $country = '';

	private $addressType;

	private function __construct(
		string $salutation,
		string $company,
		string $title,
		string $firstName,
		string $lastName,
		string $address,
		string $postcode,
		string $city,
		string $country,
		string $addressType ) {
		$this->salutation = $salutation;
		$this->company = $company;
		$this->title = $title;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->address = $address;
		$this->postcode = $postcode;
		$this->city = $city;
		$this->country = $country;
		$this->addressType = $addressType;
	}

	static public function newPersonalAddress(
		string $salutation,
		string $title,
		string $firstName,
		string $lastName,
		string $address,
		string $postcode,
		string $city,
		string $country ): self {
		self::assertNotEmpty( 'Salutation', $salutation );
		self::assertNotEmpty( 'First Name', $firstName );
		self::assertNotEmpty( 'Last Name', $lastName );
		self::assertNotEmpty( 'Address', $address );
		self::assertNotEmpty( 'Post Code', $postcode );
		self::assertNotEmpty( 'City', $city );
		self::assertNotEmpty( 'Country', $country );

		return new self( $salutation, '', $title, $firstName, $lastName, $address, $postcode, $city, $country, self::TYPE_PERSONAL );
	}

	static public function newCompanyAddress(
		string $company,
		string $address,
		string $postcode,
		string $city,
		string $country ): self {
		self::assertNotEmpty( 'Company', $company );
		self::assertNotEmpty( 'Address', $address );
		self::assertNotEmpty( 'Post Code', $postcode );
		self::assertNotEmpty( 'City', $city );
		self::assertNotEmpty( 'Country', $country );
		return new self( '', $company, '', '', '', $address, $postcode, $city, $country, self::TYPE_COMPANY );
	}

	static private function assertNotEmpty( string $field, string $value ): void {
		if ( $value === '' ) {
			throw new ChangeAddressValidationException( $field );
		}
	}

	public function isPersonalAddress(): bool {
		return $this->addressType === self::TYPE_PERSONAL;
	}

	public function isCompanyAddress(): bool {
		return $this->addressType === self::TYPE_COMPANY;
	}

	public function getSalutation(): string {
		return $this->salutation;
	}

	public function getCompany(): string {
		return $this->company;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function getFirstName(): string {
		return $this->firstName;
	}

	public function getLastName(): string {
		return $this->lastName;
	}

	public function getAddress(): string {
		return $this->address;
	}

	public function getPostcode(): string {
		return $this->postcode;
	}

	public function getCity(): string {
		return $this->city;
	}

	public function getCountry(): string {
		return $this->country;
	}

	public function getId(): ?int {
		return $this->id;
	}

}
