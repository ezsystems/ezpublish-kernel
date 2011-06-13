<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content;

$sectionIdentifier = 'content';
$sectionName = "Content section";
$sectionService = ContentRepository::get()->getSectionService();

$section = new Section();
$section->identifier = $sectionIdentifier;
$section->name = $sectionName;
try
{
    $sectionService->create( $section );
}
catch( ValidationException $e )
{
    echo "An error occured while updating the section: {$e->getMessage()}";
    exit;
}


?>
