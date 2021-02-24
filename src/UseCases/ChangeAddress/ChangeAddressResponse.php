<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress;

class ChangeAddressResponse {

	/**
	 * @var array<string>
	 */
	private array $errorMessages;

	/**
	 * @param array<string> $errorMessages
	 */
	private function __construct( array $errorMessages = [] ) {
		$this->errorMessages = $errorMessages;
	}

	/**
	 * @param array<string> $errorMessages
	 *
	 * @return ChangeAddressResponse
	 */
	public static function newErrorResponse( array $errorMessages ): self {
		return new self( $errorMessages );
	}

	public static function newSuccessResponse(): self {
		return new self();
	}

	public function isSuccess(): bool {
		return count( $this->errorMessages ) === 0;
	}

	/**
	 * @return array<string>
	 */
	public function getErrors(): array {
		return $this->errorMessages;
	}

}
