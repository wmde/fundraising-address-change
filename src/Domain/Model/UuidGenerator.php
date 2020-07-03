<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

interface UuidGenerator {
	public function generate(): string;
}
