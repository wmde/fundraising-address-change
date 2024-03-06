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
		if ( !is_string( $value ) ) {
			throw new \LogicException( 'Value from database has to be of type string' );
		}
		return match ( $value ) {
			'person' => DomainAddressType::Person,
			'company' => DomainAddressType::Company,
			default => throw new \InvalidArgumentException(
				"Could not convert address type string ({$value}) to enum"
			),
		};
	}

	public function convertToDatabaseValue( mixed $value, AbstractPlatform $platform ): string {
		if ( !( $value instanceof DomainAddressType ) ) {
			throw new \LogicException( "Value from database has to be of type " . DomainAddressType::class );
		}
		return match ( $value ) {
			DomainAddressType::Person => 'person',
			DomainAddressType::Company => 'company',
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
