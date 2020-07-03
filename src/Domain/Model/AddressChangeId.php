<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

class AddressChangeId {

	private const VALID_PATTERN = '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$';

	private string $identifier;

	private function __construct( string $id ) {
		if ( !preg_match( '/' . self::VALID_PATTERN . '/D', $id ) ) {
			throw new \InvalidArgumentException( 'Identifier must be a valid UUID ' );
		}
		$this->identifier = $id;
	}

	public static function fromString( string $id ): self {
		return new self( $id );
	}

	public function __toString(): string {
		return $this->identifier;
	}

}
