<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Data;

use WMDE\Fundraising\AddressChange\Entities\Address;

class ValidAddress {

	public static function newValidCompanyAddress(): Address {
		return Address::newCompanyAddress(
			'Widgets GmbH',
			'Industriepark 5',
			'12345',
			'Berlin',
			'DE'
		);
	}

	public static function newValidPersonalAddress(): Address {
		return Address::newPersonalAddress(
			'Herr',
			'',
			'Hanno',
			'Nym',
			'Schöne Wiese 99',
			'26789',
			'Leer',
			'DE'
		);
	}
}