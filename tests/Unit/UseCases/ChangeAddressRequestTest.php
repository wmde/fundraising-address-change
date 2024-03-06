<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\UseCases;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressRequest;

/**
 * @covers \WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressRequest
 */
class ChangeAddressRequestTest extends TestCase {
	public function testCreatesPersonalRequest(): void {
		$request = ChangeAddressRequest::newPersonalChangeAddressRequest(
			salutation: 'Herr',
			title: 'Dr.',
			firstName: 'Bruce',
			lastName: 'Wayne',
			address: 'Fledergasse 9',
			postcode: '66484',
			city: 'Battweiler',
			country: 'ZZ',
			identifier: '0caffee',
			donationReceipt: true,
			isOptOutOnly: true,
		);

		$this->assertSame( '', $request->company );
		$this->assertSame( 'Herr', $request->salutation );
		$this->assertSame( 'Dr.', $request->title );
		$this->assertSame( 'Bruce', $request->firstName );
		$this->assertSame( 'Wayne', $request->lastName );
		$this->assertSame( 'Fledergasse 9', $request->address );
		$this->assertSame( '66484', $request->postcode );
		$this->assertSame( 'Battweiler', $request->city );
		$this->assertSame( 'ZZ', $request->country );
		$this->assertSame( '0caffee', $request->identifier );
		$this->assertTrue( $request->donationReceipt );
		$this->assertTrue( $request->isOptOutOnly );
	}

	public function testCreatesCompanyRequest(): void {
		$request = ChangeAddressRequest::newCompanyChangeAddressRequest(
			company: 'Wayne Enterprises',
			address: 'Fledergasse 9',
			postcode: '66484',
			city: 'Battweiler',
			country: 'ZZ',
			identifier: '0caffee',
			donationReceipt: true,
			isOptOutOnly: true,
		);

		$this->assertSame( 'Wayne Enterprises', $request->company );
		$this->assertSame( '', $request->salutation );
		$this->assertSame( '', $request->title );
		$this->assertSame( '', $request->firstName );
		$this->assertSame( '', $request->lastName );
		$this->assertSame( 'Fledergasse 9', $request->address );
		$this->assertSame( '66484', $request->postcode );
		$this->assertSame( 'Battweiler', $request->city );
		$this->assertSame( 'ZZ', $request->country );
		$this->assertSame( '0caffee', $request->identifier );
		$this->assertTrue( $request->donationReceipt );
		$this->assertTrue( $request->isOptOutOnly );
	}

	public function testPersonFieldsAreTrimmed(): void {
		$request = ChangeAddressRequest::newPersonalChangeAddressRequest(
			salutation: ' Herr  ',
			title:  'Dr.  ',
			firstName: '  Bruce   ',
			lastName: '   Wayne   ',
			address: '   Fledergasse 9   ',
			postcode: ' 66484  ',
			city: '    Battweiler   ',
			country: '  ZZ   ',
			identifier: '0caffee     ',
			donationReceipt: true,
			isOptOutOnly: true,
		);

		$this->assertSame( 'Herr', $request->salutation );
		$this->assertSame( 'Dr.', $request->title );
		$this->assertSame( 'Bruce', $request->firstName );
		$this->assertSame( 'Wayne', $request->lastName );
		$this->assertSame( 'Fledergasse 9', $request->address );
		$this->assertSame( '66484', $request->postcode );
		$this->assertSame( 'Battweiler', $request->city );
		$this->assertSame( 'ZZ', $request->country );
		$this->assertSame( '0caffee', $request->identifier );
	}

	public function testCompanyFieldsAreTrimmed(): void {
		$request = ChangeAddressRequest::newCompanyChangeAddressRequest(
			company: ' Wayne Enterprises   ',
			address: '   Fledergasse 9   ',
			postcode: '  66484    ',
			city: ' Battweiler ',
			country: '   ZZ',
			identifier: '0caffee   ',
			donationReceipt: true,
			isOptOutOnly: true,
		);

		$this->assertSame( 'Wayne Enterprises', $request->company );
		$this->assertSame( 'Fledergasse 9', $request->address );
		$this->assertSame( '66484', $request->postcode );
		$this->assertSame( 'Battweiler', $request->city );
		$this->assertSame( 'ZZ', $request->country );
		$this->assertSame( '0caffee', $request->identifier );
	}
}
