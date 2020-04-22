<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain;

use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;

/**
 * @license GNU GPL v2+
 */
interface AddressChangeRepository {

	public function getAddressChangeByUuid( string $uuid ): ?AddressChange;

	public function storeAddressChange( AddressChange $addressChange ): void;

}
