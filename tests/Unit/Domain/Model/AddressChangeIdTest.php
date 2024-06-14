<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;

/**
 * @covers \WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId
 */
class AddressChangeIdTest extends TestCase {

	public function testConstructorAcceptValidUuids(): void {
		$uuid = '72dfed91-fa40-4af0-9e80-c6010ab29cd1';
		$addressChangeId = AddressChangeId::fromString( $uuid );

		$this->assertSame( $uuid, $addressChangeId->__toString() );
	}

	/**
	 * @dataProvider invalidUUIDProvider
	 */
	public function testThrowsExceptionsWhenUUIDIsInvalid( string $invalidUUID ): void {
		$this->expectException( \InvalidArgumentException::class );
		AddressChangeId::fromString( $invalidUUID );
	}

	/**
	 * @return \Generator<string[]>
	 */
	public static function invalidUUIDProvider(): \Generator {
		yield [ '' ];
		yield [ 'just a string' ];
		yield [ '1111222233334444-1111222233334444-1111222233334444-1111222233334444-1111222233334444' ];
		yield [ 'e-f-f-e-d' ];
		yield [ 'This-is-not-a-UUID' ];
	}

	public function testCanCheckEqualityWithStrings(): void {
		$uuid = '72dfed91-fa40-4af0-9e80-c6010ab29cd1';
		$addressChangeId = AddressChangeId::fromString( $uuid );

		$this->assertTrue( $addressChangeId->equals( $uuid ), 'IDs should compare equals' );
	}

	public function testCanCheckEqualityWithOtherID(): void {
		$uuid = '72dfed91-fa40-4af0-9e80-c6010ab29cd1';
		$addressChangeId = AddressChangeId::fromString( $uuid );
		$addressChangeIdWithSameUUID = AddressChangeId::fromString( $uuid );

		$this->assertTrue( $addressChangeId->equals( $addressChangeIdWithSameUUID ), 'IDs should compare equals' );
	}
}
