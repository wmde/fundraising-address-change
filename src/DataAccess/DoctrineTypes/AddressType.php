<?php

namespace WMDE\Fundraising\AddressChangeContext\DataAccess\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressType as DomainAddressType;

class AddressType extends Type {
	public function getSQLDeclaration( array $column, AbstractPlatform $platform ): string {
		return 'VARCHAR(10)';
	}

	public function convertToPHPValue( mixed $value, AbstractPlatform $platform ): DomainAddressType {
		return match ( $value ) {
			'person' => DomainAddressType::Person,
			'company' => DomainAddressType::Company,
			default => throw new \InvalidArgumentException(
				"Could not convert address type string ({$value}) to enum"
			),
		};
	}

	public function convertToDatabaseValue( mixed $value, AbstractPlatform $platform ): string {
		return match ( $value ) {
			DomainAddressType::Person => 'person',
			DomainAddressType::Company => 'company',
			default => throw new \InvalidArgumentException(
				"Could not convert address type enum ({$value}) to string"
			),
		};
	}

	/**
	 * @codeCoverageIgnore
	 * @return string
	 */
	public function getName(): string {
		return 'AddressType';
	}

}
