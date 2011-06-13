<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Section;

$sectionIdentifier = 'content';
$sectionName = "Content section";
$sectionService = ContentRepository::get()->getSectionService();

$section = new Section();
$section->identifier = $sectionIdentifier;
$section->name = $sectionName;
$sectionService->create( $section );


?>
