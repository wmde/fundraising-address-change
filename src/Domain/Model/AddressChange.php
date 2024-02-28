<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

use LogicException;

/**
 * An Address change record with a UUID identifier for accessing it, an optional address (if the user preformed an
 * address change) and a reference to the originating record (donation or membership).
 *
 * The recommended way to construct this class is through the AddressChangeBuilder
 */
class AddressChange {
	public const EXTERNAL_ID_TYPE_DONATION = 'donation';
	public const EXTERNAL_ID_TYPE_MEMBERSHIP = 'membership';

	public const EXPORT_STATE_NO_DATA = 'NO_DATA';
	public const EXPORT_STATE_USED_NOT_EXPORTED = 'USED_NOT_EXPORTED';
	public const EXPORT_STATE_USED_EXPORTED = 'USED_EXPORTED';

	/**
	 * @var int|null
	 * @phpstan-ignore-next-line
	 */
	private ?int $id;

	private AddressChangeId $previousIdentifier;

	private bool $donationReceipt;

	private ?\DateTimeInterface $exportDate;

	private \DateTimeInterface $createdAt;

	private \DateTimeInterface $modifiedAt;

	public function __construct(
		private readonly AddressType $addressType,
		private readonly string $externalIdType,
		private readonly int $externalId,
		private AddressChangeId $identifier,
		private ?Address $address = null,
		?\DateTime $createdAt = null
	) {
		$this->previousIdentifier = $identifier;
		if ( $externalIdType !== self::EXTERNAL_ID_TYPE_DONATION && $externalIdType !== self::EXTERNAL_ID_TYPE_MEMBERSHIP ) {
			throw new \InvalidArgumentException( 'Invalid external reference type' );
		}
		$this->exportDate = null;
		$this->createdAt = $createdAt ?? new \DateTime();
		$this->modifiedAt = clone $this->createdAt;
		$this->donationReceipt = true;
	}

	public function performAddressChange( Address $address, AddressChangeId $newIdentifier ): void {
		if ( $this->address !== null ) {
			throw new LogicException( 'Cannot perform address change for instances that already have an address.' );
		}
		$this->address = $address;
		$this->markAsModified( $newIdentifier );
	}

	public function optOutOfDonationReceipt( AddressChangeId $newIdentifier ): void {
		$this->donationReceipt = false;
		$this->markAsModified( $newIdentifier );
	}

	public function getCurrentIdentifier(): AddressChangeId {
		return $this->identifier;
	}

	public function getPreviousIdentifier(): ?AddressChangeId {
		return $this->previousIdentifier;
	}

	public function getAddress(): ?Address {
		return $this->address;
	}

	public function isPersonalAddress(): bool {
		return $this->addressType === AddressType::Person;
	}

	public function isCompanyAddress(): bool {
		return $this->addressType === AddressType::Company;
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

	private function markAsModified( AddressChangeId $newIdentifier ): void {
		$this->previousIdentifier = $this->getCurrentIdentifier();
		$this->identifier = $newIdentifier;

		$this->modifiedAt = new \DateTime();
		$this->resetExportState();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getExternalId(): int {
		return $this->externalId;
	}

	public function getExternalIdType(): string {
		return $this->externalIdType;
	}

	public function getExportState(): string {
		if ( !$this->address ) {
			return self::EXPORT_STATE_NO_DATA;
		}

		if ( $this->exportDate ) {
			return self::EXPORT_STATE_USED_EXPORTED;
		}

		return self::EXPORT_STATE_USED_NOT_EXPORTED;
	}
}
