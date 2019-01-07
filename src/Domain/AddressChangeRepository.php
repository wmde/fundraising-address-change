<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\Domain;

use WMDE\Fundraising\AddressChange\Entities\AddressChange;

/**
 * @license GNU GPL v2+
 */
interface AddressChangeRepository {

	public function getAddressChangeByUuid( string $uuid ): ?AddressChange;

}
