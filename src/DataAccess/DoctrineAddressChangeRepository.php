<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Fundraising\AddressChangeContext\Domain\AddressChangeRepository;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;

class DoctrineAddressChangeRepository implements AddressChangeRepository {

	private EntityManager $entityManager;

	public function __construct( EntityManager $entityManager ) {
		$this->entityManager = $entityManager;
	}

	public function getAddressChangeByUuid( string $uuid ): ?AddressChange {
		return $this->entityManager->getRepository( AddressChange::class )->findOneBy( [ 'identifier.identifier' => $uuid ] );
	}

	public function getAddressChangeByUuids( string $currentIdentifier, string $previousIdentifier ): ?AddressChange {
		/**
		 * @var AddressChange $result
		 */
		$result = $this->entityManager->getRepository( AddressChange::class )->createQueryBuilder( 'ac' )
			->where( 'ac.identifier.identifier = :currentIdentifier' )
			->orWhere( 'ac.previousIdentifier.identifier = :previousIdentifier' )
			->setParameter( 'currentIdentifier', $currentIdentifier )
			->setParameter( 'previousIdentifier', $previousIdentifier )
			->getQuery()
			->getOneOrNullResult();
		return $result;
	}

	public function storeAddressChange( AddressChange $addressChange ): void {
		$this->entityManager->persist( $addressChange );
		$this->entityManager->flush();
	}
}
