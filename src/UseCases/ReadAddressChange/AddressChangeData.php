<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\UseCases\ReadAddressChange;

class AddressChangeData {

	/**
	 * @param string $identifier
	 * @param string $previousIdentifier
	 * @param array<string, mixed> $address
	 * @param bool $donationReceipt
	 * @param string $exportState
	 */
	public function __construct(
		public string $identifier,
		public string $previousIdentifier,
		public array $address,
		public bool $donationReceipt,
		public string $exportState
	) {
	}
}
