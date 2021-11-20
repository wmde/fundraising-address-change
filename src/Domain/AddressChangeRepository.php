<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain;

use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;

/**
 * @license GPL-2.0-or-later
 */
interface AddressChangeRepository {

	public function getAddressChangeByUuid( string $uuid ): ?AddressChange;

	public function getAddressChangeByUuids( string $currentIdentifier, string $previousIdentifier ): ?AddressChange;

	public function storeAddressChange( AddressChange $addressChange ): void;

}
