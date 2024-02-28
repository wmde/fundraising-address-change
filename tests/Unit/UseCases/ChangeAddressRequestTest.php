<?php
declare( strict_types=1 );

namespace WMDE\Fundraising\AddressChangeContext\Tests\Unit\UseCases;

use PHPUnit\Framework\TestCase;
use WMDE\Fundraising\AddressChangeContext\Domain\Model\AddressType;
use WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressRequest;

/**
 * @covers \WMDE\Fundraising\AddressChangeContext\UseCases\ChangeAddress\ChangeAddressRequest
 */
class ChangeAddressRequestTest extends TestCase {
	public function testAccessors(): void {
		$request = new ChangeAddressRequest();
		$request->setSalutation( 'Herr' );
		$request->setTitle( 'Dr.' );
		$request->setFirstName( 'Bruce' );
		$request->setLastName( 'Wayne' );
		$request->setCompany( 'Wayne Enterprises' );
		$request->setPostcode( '66484' );
		$request->setAddress( 'Fledergasse 9' );
		$request->setCity( 'Battweiler' );
		$request->setCountry( 'ZZ' );
		$request->setAddressType( AddressType::Person );
		$request->setDonationReceipt( true );
		$request->setIdentifier( '0caffee' );
		$request->setIsOptOutOnly( false );
		$optOutRequest = new ChangeAddressRequest();
		$optOutRequest->setIsOptOutOnly( true );

		$this->assertSame( 'Herr', $request->getSalutation() );
		$this->assertSame( 'Dr.', $request->getTitle() );
		$this->assertSame( 'Bruce', $request->getFirstName() );
		$this->assertSame( 'Wayne', $request->getLastName() );
		$this->assertSame( 'Wayne Enterprises', $request->getCompany() );
		$this->assertSame( 'Fledergasse 9', $request->getAddress() );
		$this->assertSame( '66484', $request->getPostcode() );
		$this->assertSame( 'Battweiler', $request->getCity() );
		$this->assertSame( 'ZZ', $request->getCountry() );
		$this->assertSame( AddressType::Person, $request->getAddressType() );
		$this->assertSame( '0caffee', $request->getIdentifier() );
		$this->assertTrue( $request->isPersonal() );
		$this->assertFalse( $request->isCompany() );
		$this->assertFalse( $request->isOptedOutOfDonationReceipt() );
		$this->assertTrue( $request->hasAddressChangeData() );
		$this->assertFalse( $optOutRequest->hasAddressChangeData() );
	}

	public function testStringFieldsAreTrimmed(): void {
		$request = new ChangeAddressRequest();
		$request->setSalutation( ' Herr ' );
		$request->setTitle( 'Dr. ' );
		$request->setFirstName( '    Bruce ' );
		$request->setLastName( 'Wayne ' );
		$request->setCompany( ' Wayne Enterprises' );
		$request->setPostcode( "66484 \n" );
		$request->setAddress( "\t   Fledergasse 9 " );
		$request->setCity( '  Battweiler  ' );
		$request->setCountry( ' ZZ  ' );
		$request->setIdentifier( '     0caffee    ' );

		$this->assertSame( 'Herr', $request->getSalutation() );
		$this->assertSame( 'Dr.', $request->getTitle() );
		$this->assertSame( 'Bruce', $request->getFirstName() );
		$this->assertSame( 'Wayne', $request->getLastName() );
		$this->assertSame( 'Wayne Enterprises', $request->getCompany() );
		$this->assertSame( 'Fledergasse 9', $request->getAddress() );
		$this->assertSame( '66484', $request->getPostcode() );
		$this->assertSame( 'Battweiler', $request->getCity() );
		$this->assertSame( 'ZZ', $request->getCountry() );
		$this->assertSame( '0caffee', $request->getIdentifier() );
	}
}
