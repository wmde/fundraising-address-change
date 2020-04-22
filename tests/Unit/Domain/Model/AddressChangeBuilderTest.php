<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeId;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\UuidGenerator;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\Address;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChange;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressChangeBuilder;

class AddressChangeBuilderTest extends TestCase implements UuidGenerator {

	public function testGivenParametersForCreateTheyArePassedToAddressChange(): void {
		$identifier = AddressChangeId::fromString( '77c12190-d97a-4564-9d88-160be51dd134' );
		$address = Address::newCompanyAddress( 'Bank of Duckburg', 'At the end of the road', '1234', 'Duckburg', 'DE' );
		$addressChange = AddressChangeBuilder::create( $identifier, $address )->forPerson()->forDonation( 1 )->build();

		$this->assertSame( $identifier, $addressChange->getCurrentIdentifier() );
		$this->assertSame( $address, $addressChange->getAddress() );
	}

	public function testBuildPersonalAddressChangeForDonation(): void {
		$addressChange = AddressChangeBuilder::create()->forPerson()->forDonation( 1 )->build();

		$this->assertTrue( $addressChange->isPersonalAddress() );
		$this->assertSame( 1, $addressChange->getExternalId() );
		$this->assertSame( AddressChange::EXTERNAL_ID_TYPE_DONATION, $addressChange->getExternalIdType() );
	}

	public function testBuildCompanyAddressChangeForDonation(): void {
		$addressChange = AddressChangeBuilder::create()->forCompany()->forDonation( 1 )->build();

		$this->assertTrue( $addressChange->isCompanyAddress() );
	}

	public function testBuildPersonalAddressChangeForMembership(): void {
		$addressChange = AddressChangeBuilder::create()->forPerson()->forMembership( 3 )->build();

		$this->assertSame( 3, $addressChange->getExternalId() );
		$this->assertSame( AddressChange::EXTERNAL_ID_TYPE_MEMBERSHIP, $addressChange->getExternalIdType() );
	}

	public function testPersonCannotBeChangedToCompany(): void {
		$this->expectException( \RuntimeException::class );

		AddressChangeBuilder::create()->forPerson()->forCompany();
	}

	public function testCompanyCannotBeChangedToPerson(): void {
		$this->expectException( \RuntimeException::class );

		AddressChangeBuilder::create()->forCompany()->forPerson();
	}

	public function testDonationCannotBeChangedToMembership(): void {
		$this->expectException( \RuntimeException::class );

		AddressChangeBuilder::create()->forDonation( 1 )->forMembership( 1 );
	}

	public function testMembershipCannotBeChangedToDonation(): void {
		$this->expectException( \RuntimeException::class );

		AddressChangeBuilder::create()->forMembership( 1 )->forDonation( 1 );
	}

	public function testAddressAndReferenceTypeHaveToBeSpecified(): void {
		$this->expectException( \RuntimeException::class );

		AddressChangeBuilder::create()->build();
	}

	public function testUuidGeneratorCanBeSwitchedOut(): void {
		AddressChangeBuilder::setUuidGenerator( $this );
		$addressChange = $addressChange = AddressChangeBuilder::create()->forPerson()->forDonation( 1 )->build();

		$this->assertSame( 'c956688a-89e8-41b7-b93e-7e4cf3d6c826', (string) $addressChange->getCurrentIdentifier() );
	}

	public function generate(): string {
		return 'c956688a-89e8-41b7-b93e-7e4cf3d6c826';
	}


}
