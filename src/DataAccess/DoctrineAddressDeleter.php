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

		$idQuery = $this->entityManager->createQueryBuilder();
		$idQuery->select( 'DISTINCT IDENTITY(ac_id.address)' )
			->from( AddressChange::class, 'ac_id' )
			->where( $idQuery->expr()->orX(
				$idQuery->expr()->isNotNull( 'ac_id.exportDate' ),
				$idQuery->expr()->lte( 'ac_id.modifiedAt', ':cutoffDate' )
			) );

		$qb = $this->entityManager->createQueryBuilder();
		$qb->delete( Address::class, 'a' )
			->where( $qb->expr()->in( 'a.id', $idQuery->getDQL() ) )
			->setParameter( 'cutoffDate', $cutoffDate )
			->getQuery()
			->execute();

		$qb = $this->entityManager->createQueryBuilder();
		$qb->update( AddressChange::class, 'ac' )
			->set( 'ac.exportDate', 'NULL' )
			->set( 'ac.address', 'NULL' )
			->where( $qb->expr()->in( 'IDENTITY(ac.address)', $idQuery->getDQL() ) )
			->setParameter( 'cutoffDate', $cutoffDate )
			->getQuery()
			->execute();
	}
}
