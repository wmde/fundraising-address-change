<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\AddressChangeContext;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;

/**
 * @licence GNU GPL v2+
 */
class AddressChangeContextFactory {

	/**
	 * Use this constant for MappingDriverChain::addDriver
	 */
	public const ENTITY_NAMESPACE = 'WMDE\Fundraising\AddressChangeContext\Domain\Model';

	private const DOCTRINE_CLASS_MAPPING_DIRECTORY = __DIR__ . '/../config/DoctrineClassMapping';

	public function newMappingDriver(): MappingDriver {
		// We're only calling this for the side effect of adding Mapping/Driver/DoctrineAnnotations.php
		// to the AnnotationRegistry. When AnnotationRegistry is deprecated with Doctrine Annotations 2.0,
		// use $this->annotationReader instead
		return new XmlDriver( self::DOCTRINE_CLASS_MAPPING_DIRECTORY );
	}

	public function newEventSubscribers(): array {
		// Currently we don't have event subscribers, but this method helps to keep a consistent interface
		// with all the other context factories of the bounded contexts.
		return [];
	}

}
