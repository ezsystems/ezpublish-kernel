<?php
use ezp\Base\ServiceContainer;

$sc = new ServiceContainer();
$sectionService = $sc->getRepository()->getSectionService();
$locationService = $sc->getRepository()->getLocationService();

$sectionId = 2;
$locationId = 60;

$location = $locationService->load( $locationId );
$section = $sectionService->load( $sectionId );

$locationService->assignSection( $location, $section );

?>
