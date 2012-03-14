<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$sectionService = $sc->getRepository()->getSectionService();
$locationService = $sc->getRepository()->getLocationService();

$sectionId = 2;
$locationId = 60;

$location = $locationService->load( $locationId );
$section = $sectionService->load( $sectionId );

$locationService->assignSection( $location, $section );
