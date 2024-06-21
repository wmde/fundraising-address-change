<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\Address;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressValidationException;

#[CoversClass( Address::class )]
class AddressTest extends TestCase {

	public function testWhenValidFieldValuesAreUsedForPersonalAddress_addressIsCreated(): void {
		$address = Address::newPersonalAddress(
			'Herr',
			'Prof. Dr.',
			'Tester',
			'Testfamily',
			'Test Street 123',
			'12345',
			'Test City',
			'Test Country'
		);
		$this->assertSame( 'Herr', $address->getSalutation() );
		$this->assertSame( 'Prof. Dr.', $address->getTitle() );
		$this->assertSame( 'Tester', $address->getFirstName() );
		$this->assertSame( 'Testfamily', $address->getLastName() );
		$this->assertSame( 'Test Street 123', $address->getAddress() );
		$this->assertSame( '12345', $address->getPostcode() );
		$this->assertSame( 'Test City', $address->getCity() );
		$this->assertSame( 'Test Country', $address->getCountry() );
	}

	public function testWhenValidFieldValuesAreUsedForCompanyAddress_addressIsCreated(): void {
		$address = Address::newCompanyAddress(
			'Test Company',
			'Test Street 123',
			'12345',
			'Test City',
			'Test Country'
		);
		$this->assertSame( 'Test Company', $address->getCompany() );
		$this->assertSame( 'Test Street 123', $address->getAddress() );
		$this->assertSame( '12345', $address->getPostcode() );
		$this->assertSame( 'Test City', $address->getCity() );
		$this->assertSame( 'Test Country', $address->getCountry() );
	}

	#[DataProvider( 'emptyPersonFieldTestProvider' )]
	public function testWhenNewPersonalAddressWithEmptyFieldsIsCreated_exceptionIsThrownOnEmptyFields(
		string $testField,
		string $salutation,
		string $title,
		string $firstName,
		string $lastName,
		string $address,
		string $postcode,
		string $city,
		string $country ): void {
		$this->expectException( ChangeAddressValidationException::class );
		$this->expectExceptionMessage( sprintf( 'Invalid value for field "%s".', $testField ) );
		Address::newPersonalAddress( $salutation, $title, $firstName, $lastName, $address, $postcode, $city, $country );
	}

	#[DataProvider( 'emptyCompanyFieldTestProvider' )]
	public function testWhenNewCompanyAddressWithEmptyFieldsIsCreated_exceptionIsThrownOnEmptyFields(
		string $testField,
		string $company,
		string $address,
		string $postcode,
		string $city,
		string $country ): void {
		$this->expectException( ChangeAddressValidationException::class );
		$this->expectExceptionMessage( sprintf( 'Invalid value for field "%s".', $testField ) );
		Address::newCompanyAddress( $company, $address, $postcode, $city, $country );
	}

	/**
	 * @return \Generator<string[]>
	 */
	public static function emptyPersonFieldTestProvider(): \Generator {
		yield [ 'Salutation', '', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'First Name', 'Herr', 'Prof. Dr.', '', 'Testfamily', 'Test Address 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'Last Name', 'Herr', 'Prof. Dr.', 'Testdude', '', 'Test Address 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'Address', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', '', '12345', 'Test City', 'Test Country' ];
		yield [ 'Post Code', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '', 'Test City', 'Test Country' ];
		yield [ 'City', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '12345', '', 'Test Country' ];
		yield [ 'Country', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '12345', 'Test City', '' ];
	}

	/**
	 * @return \Generator<string[]>
	 */
	public static function emptyCompanyFieldTestProvider(): \Generator {
		yield [ 'Company', '', 'Test Street 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'Address', 'Test Company', '', '12345', 'Test City', 'Test Country' ];
		yield [ 'Post Code', 'Test Company', 'Test Street 123', '', 'Test City', 'Test Country' ];
		yield [ 'City', 'Test Company', 'Test Street 123', '12345', '', 'Test Country' ];
		yield [ 'Country', 'Test Company', 'Test Street 123', '12345', 'Test City', '' ];
	}
}
