<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Section;

$sectionId = 2;
$locationId = 60;
$sectionService = ContentRepository::get()->getSectionService();
$locationService = ContentRepository::get()->getLocationService();

$location = $locationService->load( $locationId );
$section = $sectionService->load( $sectionId );

$locationService->assignSection( $location, $section );

?>
