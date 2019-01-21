<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChange\Domain\Model\Address;
use WMDE\Fundraising\AddressChange\UseCases\ChangeAddress\ChangeAddressValidationException;

/**
 * @covers \WMDE\Fundraising\AddressChange\Domain\Model\Address
 */
class AddressTest extends TestCase {

	public function testWhenValidFieldValuesAreUsedForPersonalAddress_addressIsCreated() {
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
		$this->assertTrue(
			$address->isPersonalAddress()
		);
	}

	public function testWhenValidFieldValuesAreUsedForCompanyAddress_addressIsCreated() {
		$address = Address::newCompanyAddress(
			'Test Company',
			'Test Street 123',
			'12345',
			'Test City',
			'Test Country'
		);
		$this->assertTrue(
			$address->isCompanyAddress()
		);
	}

	/**
	 * @dataProvider emptyPersonFieldTestProvider
	 */
	public function testWhenNewPersonalAddressWithEmptyFieldsIsCreated_exceptionIsThrownOnEmptyFields(
		string $testField,
		string $salutation,
		string $title,
		string $firstName,
		string $lastName,
		string $address,
		string $postcode,
		string $city,
		string $country ) {
		$this->expectException( ChangeAddressValidationException::class );
		$this->expectExceptionMessage( sprintf( 'Invalid value for field "%s".', $testField ) );
		Address::newPersonalAddress( $salutation, $title, $firstName, $lastName, $address, $postcode, $city, $country );
	}

	/**
	 * @dataProvider emptyCompanyFieldTestProvider
	 */
	public function testWhenNewCompanyAddressWithEmptyFieldsIsCreated_exceptionIsThrownOnEmptyFields(
		string $testField,
		string $company,
		string $address,
		string $postcode,
		string $city,
		string $country ) {
		$this->expectException( ChangeAddressValidationException::class );
		$this->expectExceptionMessage( sprintf( 'Invalid value for field "%s".', $testField ) );
		Address::newCompanyAddress( $company, $address, $postcode, $city, $country );
	}

	public function emptyPersonFieldTestProvider(): \Generator {
		yield [ 'Salutation', '', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'First Name', 'Herr', 'Prof. Dr.', '', 'Testfamily', 'Test Address 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'Last Name', 'Herr', 'Prof. Dr.', 'Testdude', '', 'Test Address 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'Address', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', '', '12345', 'Test City', 'Test Country' ];
		yield [ 'Post Code', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '', 'Test City', 'Test Country' ];
		yield [ 'City', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '12345', '', 'Test Country' ];
		yield [ 'Country', 'Herr', 'Prof. Dr.', 'Testdude', 'Testfamily', 'Test Address 123', '12345', 'Test City', '' ];
	}

	public function emptyCompanyFieldTestProvider(): \Generator {
		yield [ 'Company', '', 'Test Street 123', '12345', 'Test City', 'Test Country' ];
		yield [ 'Address', 'Test Company', '', '12345', 'Test City', 'Test Country' ];
		yield [ 'Post Code', 'Test Company', 'Test Street 123', '', 'Test City', 'Test Country' ];
		yield [ 'City', 'Test Company', 'Test Street 123', '12345', '', 'Test Country' ];
		yield [ 'Country', 'Test Company', 'Test Street 123', '12345', 'Test City', '' ];
	}
}
