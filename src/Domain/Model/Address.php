<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressValidationException;

class Address {

	/**
	 * @var int|null
	 * @phpstan-ignore-next-line
	 */
	private ?int $id;

	private string $salutation;

	private string $company;

	private string $title;

	private string $firstName = '';

	private string $lastName = '';

	private string $address;

	private string $postcode;

	private string $city;

	private string $country = '';

	private function __construct(
		string $salutation,
		string $company,
		string $title,
		string $firstName,
		string $lastName,
		string $address,
		string $postcode,
		string $city,
		string $country
	) {
		$this->salutation = $salutation;
		$this->company = $company;
		$this->title = $title;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->address = $address;
		$this->postcode = $postcode;
		$this->city = $city;
		$this->country = $country;
	}

	public static function newPersonalAddress(
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

		return new self( $salutation, '', $title, $firstName, $lastName, $address, $postcode, $city, $country );
	}

	public static function newCompanyAddress(
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
		return new self( '', $company, '', '', '', $address, $postcode, $city, $country );
	}

	private static function assertNotEmpty( string $field, string $value ): void {
		if ( $value === '' ) {
			throw new ChangeAddressValidationException( $field );
		}
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
