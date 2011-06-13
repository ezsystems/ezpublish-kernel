<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Section;

$sectionId = 1;
$sectionService = ContentRepository::get()->getSectionService();
$section = $sectionService->load( $sectionId );
$section->name = "New section name";
$sectionService->update( $section );


?>
