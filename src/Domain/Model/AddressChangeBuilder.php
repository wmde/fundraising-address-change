<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

use WMDE\Fundraising\AddressChangeContext\Infrastructure\RandomUuidGenerator;

class AddressChangeBuilder {

	private static ?UuidGenerator $uuidGenerator = null;

	private ?AddressType $addressType;
	private ?string $referenceType;
	private ?int $referenceId;
	private AddressChangeId $identifier;
	private ?Address $address;
	private ?\DateTime $createdAt;

	private function __construct( AddressChangeId $identifier = null, ?Address $address = null, ?\DateTime $createdAt = null ) {
		$this->identifier = $identifier;
		$this->address = $address;
		$this->createdAt = $createdAt;
		$this->addressType = null;
		$this->referenceType = null;
		$this->referenceId = null;
	}

	public static function create( ?AddressChangeId $identifier = null, ?Address $address = null, ?\DateTime $createdAt = null ): self {
		if ( $identifier === null ) {
			$identifier = AddressChangeId::fromString( self::generateUuid() );
		}
		return new self( $identifier, $address, $createdAt );
	}

	public function forPerson(): self {
		return $this->setAddressType( AddressType::Person );
	}

	public function forCompany(): self {
		return $this->setAddressType( AddressType::Company );
	}

	public function setAddressType( AddressType $addressType ): self {
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
		return new AddressChange( $this->addressType, $this->referenceType, $this->referenceId, $this->identifier, $this->address, $this->createdAt );
	}

	public static function setUuidGenerator( UuidGenerator $generator ): void {
		self::$uuidGenerator = $generator;
	}

	public static function generateUuid(): string {
		if ( self::$uuidGenerator === null ) {
			self::$uuidGenerator = new RandomUuidGenerator();
		}
		return call_user_func( [ self::$uuidGenerator, 'generate' ] );
	}

}
