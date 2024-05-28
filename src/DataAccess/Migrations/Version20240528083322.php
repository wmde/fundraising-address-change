<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\DataAccess\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240528083322 extends AbstractMigration {

	public function getDescription(): string {
		return 'Add indexes for full text search on the Address table';
	}

	public function up( Schema $schema ): void {
		$table = $schema->getTable( 'address' );
		$table->addIndex( [ 'first_name' ], 'idx_ac_first_name' );
		$table->addIndex( [ 'last_name' ], 'idx_ac_last_name' );
		$table->addIndex( [ 'street' ], 'idx_ac_street' );
		$table->addIndex( [ 'postcode' ], 'idx_ac_postcode' );
		$table->addIndex( [ 'city' ], 'idx_ac_city' );
	}

	public function down( Schema $schema ): void {
		$table = $schema->getTable( 'address' );
		$table->dropIndex( 'idx_ac_first_name' );
		$table->dropIndex( 'idx_ac_last_name' );
		$table->dropIndex( 'idx_ac_street' );
		$table->dropIndex( 'idx_ac_postcode' );
		$table->dropIndex( 'idx_ac_city' );
	}
}
