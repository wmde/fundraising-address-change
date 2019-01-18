<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChange\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\AddressChange\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChange\Domain\Model\AddressChange;

class DoctrineAddressChangeRepository implements AddressChangeRepository {

	private $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function getAddressChangeByUuid( string $uuid ): ?AddressChange {
		return $this->entityManager->getRepository( AddressChange::class )->findOneBy( [ 'identifier' => $uuid ] );
	}
}