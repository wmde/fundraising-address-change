<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Infrastructure;

use Ramsey\Uuid\Uuid;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\UuidGenerator;

class RandomUuidGenerator implements UuidGenerator {

	public function generate(): string {
		return Uuid::uuid4()->toString();
	}

}
