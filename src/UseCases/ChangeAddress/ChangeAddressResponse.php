<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\UseCases\ChangeAddress;

class ChangeAddressResponse {

	private $errorMessages;

	private function __construct( array $errorMessages = [] ) {
		$this->errorMessages = $errorMessages;
	}

	public static function newErrorResponse( array $errorMessages ): self {
		return new self( $errorMessages );
	}

	public static function newSuccessResponse(): self {
		return new self();
	}

	public function isSuccess(): bool {
		return count( $this->errorMessages ) === 0;
	}

	public function getErrors(): array {
		return $this->errorMessages;
	}

}