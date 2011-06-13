<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Section;

$sectionId = 2;
$locationId = 60;
$sectionService = ContentRepository::get()->getSectionService();
$subtreeService = ContentRepository::get()->getSubtreeService();

$location = $subtreeService->load( $locationId );
$section = $sectionService->load( $sectionId );

$subtreeService->assignSection( $location, $section );

?>
