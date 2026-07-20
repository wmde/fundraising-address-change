<?php

declare( strict_types=1 );

namespace WMDE\Fundraising\AddressChangeContext\DataAccess\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260720113122 extends AbstractMigration {
	public function getDescription(): string {
		return 'Make all Address columns non-nullable';
	}

	public function up( Schema $schema ): void {
		// some columns were already non-nullable
		$table = $schema->getTable( 'address' );
		$table->getColumn( 'salutation' )
			->setNotnull( true )
			->setDefault( '' )
			->setFixed( false );
		$table->getColumn( 'company' )
			->setNotnull( true )
			->setDefault( '' )
			->setFixed( false );
		$table->getColumn( 'title' )
			->setNotnull( true )
			->setDefault( '' )
			->setFixed( false );
		$table->getColumn( 'street' )
			->setNotnull( true )
			->setDefault( '' )
			->setFixed( false );
		$table->getColumn( 'postcode' )
			->setNotnull( true )
			->setDefault( '' )
			->setFixed( false );
		$table->getColumn( 'city' )
			->setNotnull( true )
			->setDefault( '' )
			->setFixed( false );

		$table->getColumn( 'first_name' )->setFixed( false );
		$table->getColumn( 'last_name' )->setFixed( false );
	}

	public function down( Schema $schema ): void {
		$table = $schema->getTable( 'address' );
		$table->getColumn( 'salutation' )
			->setNotnull( false )
			->setFixed( true );
		$table->getColumn( 'company' )
			->setNotnull( false )
			->setFixed( true );
		$table->getColumn( 'title' )
			->setNotnull( false )
			->setFixed( true );
		$table->getColumn( 'street' )
			->setNotnull( false )
			->setFixed( true );
		$table->getColumn( 'postcode' )
			->setNotnull( false )
			->setFixed( true );
		$table->getColumn( 'city' )
			->setNotnull( false )
			->setFixed( true );

		$table->getColumn( 'first_name' )->setFixed( true );
		$table->getColumn( 'last_name' )->setFixed( true );
	}
}
