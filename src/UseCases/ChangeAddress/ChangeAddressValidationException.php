<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\UseCases\ChangeAddress;

class ChangeAddressValidationException extends \Exception {

	public function __construct( string $field ) {
		parent::__construct(
			sprintf( 'Invalid value for field "%s".', $field )
		);
	}
}