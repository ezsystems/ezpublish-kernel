<?php
use eZ\Publish\SPI\Persistence\Content\Relation;

$relation = new Relation();
$relation->id = 1;
$relation->sourceContentId = 1;
$relation->sourceContentVersionNo = 1;
$relation->type = 1;
$relation->destinationContentId = 2;

return array( 1 => $relation );