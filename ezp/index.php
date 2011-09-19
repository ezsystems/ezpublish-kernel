<?php
/**
 * ez Publish - Public API Prototype
 */
namespace ezp\Content;
use ezp\Base\Configuration,
    ezp\Base\Autoloader,
    ezp\Content,
    ezp\Content\Type\FieldDefinition,
    ezp\User;

chdir( '../' );
require 'testsBootstrap.php';

// Create Type manually for test
$contentType = new Type();
$contentType->identifier = 'article';

// Add some fields
$fields = array(
    'title' => array( 'ezstring', 'New Article' ),
    'tags' => array( 'ezkeyword', '' )
);
foreach ( $fields as $identifier => $data )
{
    $field = new FieldDefinition( $contentType, $data[0] );
    $field->identifier = $identifier;
    $field->defaultValue = $data[1];
    $contentType->fields[] = $field;
}

// Create section
$section = new Section();
$section->identifier = 'standard';
$section->name = "Standard";

// Create Content object
$content = new Content( $contentType, new User( 10 ) );
$content->ownerId = 10;
$content->setSection( $section );

$content->fields['tags'] = 'ezpublish, demo, public, api';
//$content->fields['title'] = 'My new Article';
// shortcut for: $content->fields['title']->value = 'My new Article';

echo "Content id: {$content->id}<br />";

echo "Fields:<br />";
foreach ( $content->fields as $identifier => $field )
{
    echo "$identifier: {$field->value}<br />";
}

