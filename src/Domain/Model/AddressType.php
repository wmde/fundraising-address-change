<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\AddressChangeContext\Domain\Model;

enum AddressType {
	case Person;
	case Company;
}
