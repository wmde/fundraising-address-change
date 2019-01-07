<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests;

/**
 * @license GNU GPL v2+
 */
class TestEnvironment {

	private $config;
	private $factory;

	public static function newInstance(): self {
		$environment = new self(
			[
				'db' => [
					'driver' => 'pdo_sqlite',
					'memory' => true,
				],
				'var-path' => '/tmp',
				'token-length' => 16,
				'token-validity-timestamp' => 'PT4H',
			]
		);

		$environment->install();

		return $environment;
	}

	private function __construct( array $config ) {
		$this->config = $config;
		$this->factory = new AddressChangeContextFactory( $this->config );
	}

	private function install(): void {
		$installer = $this->factory->newInstaller();

		try {
			$installer->uninstall();
		}
		catch ( \Exception $ex ) {
		}

		$installer->install();
	}

	public function getFactory(): AddressChangeContextFactory {
		return $this->factory;
	}

}