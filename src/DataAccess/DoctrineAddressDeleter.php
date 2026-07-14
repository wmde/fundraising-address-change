<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\DataAccess;

use Doctrine\ORM\EntityManager;
use WMDE\Clock\Clock;
use WMDE\Fundraising\AddressChangeContext\Domain\AddressDeleter;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\Address;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;

class DoctrineAddressDeleter implements AddressDeleter {

	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly Clock $clock,
		private readonly \DateInterval $exportGracePeriod,
	) {
	}

	public function deleteAll(): void {
		$cutoffDate = $this->clock->now()->sub( $this->exportGracePeriod );

		$qb = $this->entityManager->createQueryBuilder();
		$qb->select( 'a.id' )
			->from( Address::class, 'a' )
			->leftJoin( AddressChange::class, 'ac' )
			->where( $qb->expr()->orX(
				$qb->expr()->isNotNull( 'ac.exportDate' ),
				$qb->expr()->lte( 'ac.modifiedAt', ':cutoffDate' )
			) )
			->setParameter( 'cutoffDate', $cutoffDate );

		$ids = $qb->getQuery()->getResult();

		$qb = $this->entityManager->createQueryBuilder();
		$qb->delete( Address::class, 'a' )
			->where( $qb->expr()->in( 'a.id', ':ids' ) )
			->setParameter( 'ids', $ids )
			->getQuery()
			->execute();

		$qb = $this->entityManager->createQueryBuilder();
		$qb->update( AddressChange::class, 'ac' )
			->set( 'ac.exportDate', 'NULL' )
			->set( 'ac.address', 'NULL' )
			->where( $qb->expr()->in( 'IDENTITY(ac.address)', ':ids' ) )
			->setParameter( 'ids', $ids )
			->getQuery()
			->execute();
	}
}
