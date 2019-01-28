<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\Domain;

use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;

/**
 * @license GNU GPL v2+
 */
interface AddressChangeRepository {

	public function getAddressChangeByUuid( string $uuid ): ?AddressChange;

	public function storeAddressChange( AddressChange $addressChange ): void;

}
