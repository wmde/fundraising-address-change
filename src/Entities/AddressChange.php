<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\Entities;

use Ramsey\Uuid\Uuid;

class AddressChange {

	public const ADDRESS_TYPE_PERSON = 'person';
	public const ADDRESS_TYPE_COMPANY = 'company';

	private $id;

	private $identifier;

	private $previousIdentifier;

	private $address;

	private $addressType;

	private function __construct( string $addressType, ?string $identifier = null, ?Address $address = null ) {
		$this->addressType = $addressType;
		$this->identifier = $identifier;
		$this->address = $address;
		if ( $this->identifier === null ) {
			$this->generateUuid();
		}
	}

	public static function createNewPersonAddressChange( ?string $identifier = null, ?Address $address = null ): self {
		return new AddressChange( self::ADDRESS_TYPE_PERSON, $identifier, $address );
	}

	public static function createNewCompanyAddressChange( ?string $identifier = null, ?Address $address = null ): self {
		return new AddressChange( self::ADDRESS_TYPE_COMPANY, $identifier, $address );
	}

	private function generateUuid(): void {
		$this->identifier = Uuid::uuid4()->toString();
	}

	public function performAddressChange(): void {
		$this->previousIdentifier = $this->getCurrentIdentifier();
		$this->generateUuid();
	}

	public function getCurrentIdentifier(): string {
		if ( $this->identifier === null ) {
			$this->generateUuid();
		}
		return $this->identifier;
	}

	public function getPreviousIdentifier(): ?string {
		return $this->previousIdentifier;
	}

	public function getAddress(): ?Address {
		return $this->address;
	}

	public function isPersonalAddress(): bool {
		return $this->addressType === self::ADDRESS_TYPE_PERSON;
	}

	public function isCompanyAddress(): bool {
		return $this->addressType === self::ADDRESS_TYPE_COMPANY;
	}
}
