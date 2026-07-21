<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain;

interface AddressDeleter {
	public function deleteAll(): void;
}
