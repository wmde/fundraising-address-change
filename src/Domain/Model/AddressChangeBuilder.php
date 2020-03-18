<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

use WMDE\Fundraising\AddressChange\Domain\Model\Address;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;

class AddressChangeBuilder {

	/**
	 * @var string
	 */
	private $addressType;

	/**
	 * @var string
	 */
	private $referenceType;

	/**
	 * @var int
	 */
	private $referenceId;

	private $id;
	private $address;
	private $createdAt;

	public function __construct( ?string $identifier = null, ?Address $address = null, ?\DateTime $createdAt = null ) {
		$this->id = $identifier;
		$this->address = $address;
		$this->createdAt = $createdAt;
	}

	public static function create( ?string $identifier = null, ?Address $address = null, ?\DateTime $createdAt = null ): self {
		return new self( $identifier, $address, $createdAt );
	}

	public function forPerson(): self {
		return $this->setAddressType( AddressChange::ADDRESS_TYPE_PERSON );
	}

	public function forCompany(): self {
		return $this->setAddressType( AddressChange::ADDRESS_TYPE_COMPANY );
	}

	private function setAddressType( string $addressType ): self {
		if ( $this->addressType !== null ) {
			throw new \RuntimeException( 'You can only specify address type once' );
		}
		$this->addressType = $addressType;
		return $this;
	}

	public function forDonation( int $donationId ): self {
		return $this->setReference( $donationId, AddressChange::EXTERNAL_ID_TYPE_DONATION );
	}

	public function forMembership( int $membershipId ): self {
		return $this->setReference( $membershipId, AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP );
	}

	private function setReference( int $referenceId, string $referenceType ): self {
		if ( $this->referenceType !== null ) {
			throw new \RuntimeException( 'You can only specify reference type once' );
		}
		$this->referenceType = $referenceType;
		$this->referenceId = $referenceId;
		return $this;
	}

	public function build(): AddressChange {
		if ( $this->referenceType === null || $this->addressType === null ) {
			throw new \RuntimeException( 'You must specify address type and reference' );
		}
		return new AddressChange( $this->addressType, $this->referenceType, $this->referenceId, $this->id, $this->address, $this->createdAt );
	}

}