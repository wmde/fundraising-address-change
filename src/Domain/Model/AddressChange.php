<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\Domain\Model;

use LogicException;
use Ramsey\Uuid\Uuid;

class AddressChange {

	public const ADDRESS_TYPE_PERSON = 'person';
	public const ADDRESS_TYPE_COMPANY = 'company';

	private $id;

	private $identifier;

	private $previousIdentifier;

	private $address;

	private $addressType;

	private $donationReceipt;

	private $exportDate;

	private $createdAt;

	private $modifiedAt;

	private function __construct( string $addressType, ?string $identifier = null, ?Address $address = null, ?\DateTime $createdAt = null ) {
		$this->addressType = $addressType;
		$this->identifier = $identifier;
		$this->address = $address;
		if ( $identifier === null ) {
			$this->generateUuid();
		} elseif ( !Uuid::isValid( $identifier ) ) {
			throw new \InvalidArgumentException( 'Identifier must be a valid UUID' );
		}
		$this->createdAt = $createdAt ?? new \DateTime();
		$this->modifiedAt = clone( $this->createdAt );
		$this->donationReceipt = true;
	}

	public static function createNewPersonAddressChange( ?string $identifier = null, ?Address $address = null, ?\DateTime $createdAt = null ): self {
		return new AddressChange( self::ADDRESS_TYPE_PERSON, $identifier, $address, $createdAt );
	}

	public static function createNewCompanyAddressChange( ?string $identifier = null, ?Address $address = null, ?\DateTime $createdAt = null ): self {
		return new AddressChange( self::ADDRESS_TYPE_COMPANY, $identifier, $address, $createdAt );
	}

	private function generateUuid(): void {
		$this->identifier = Uuid::uuid4()->toString();
	}

	public function performAddressChange( Address $address ): void {
		if ( $this->address !== null ) {
			throw new LogicException( 'Cannot perform address change for instances that already have an address.' );
		}
		$this->address = $address;
		$this->markAsModified();
	}

	public function optOutOfDonationReceipt(): void {
		$this->donationReceipt = false;
		$this->markAsModified();
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

	public function isOptedIntoDonationReceipt(): bool {
		return $this->donationReceipt;
	}

	public function markAsExported(): void {
		if ( $this->isExported() ) {
			throw new LogicException( 'Address changes can only be exported once.' );
		}
		$this->exportDate = new \DateTime();
	}

	private function resetExportState(): void {
		$this->exportDate = null;
	}

	public function isExported(): bool {
		return $this->exportDate !== null;
	}

	public function isModified(): bool {
		return $this->createdAt < $this->modifiedAt;
	}

	private function markAsModified() {
		if ( !$this->isModified() ) {
			$this->previousIdentifier = $this->getCurrentIdentifier();
			$this->generateUuid();
		}
		$this->modifiedAt = new \DateTime();
		$this->resetExportState();
	}
}
