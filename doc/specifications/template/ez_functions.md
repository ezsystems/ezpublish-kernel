# Helper Twig functions for use with eZ Publish

Working with eZ Publish in twig introduces a set of conventions and complex data structures that
you would typically have to write a lot of boilerplate code, typically in PHP, to be able to use.

The following template helpers tries to make it easier to deal with objects from eZ Publish Repository,
below they are organized by category.


## Translation helpers

A group of helpers that gives you access to translated properties on eZ Repository objects.

Common for them all is that last parameter allows you to force language, otherwise they will use
the system languages as defined in current site-access settings. In both cases main language is appended
if always available flag is non-existing or true on the object.

Example: In the case of Content objects, which have a always available flag, if true and mainLanguage is 'eng-US' then
         if user provides content object and forces language to be 'jpn-JP', system will return name in Japanese
         language if it exists, if not it will fallback to 'eng-US'.

Note: PHP Object names referred to in these examples exist in the \eZ\Publish\API\Repository\Values\Content namespace!

* ez_content_name

  _Since 5.2_

  `string = ez_content_name( Content|ContentInfo $content[, string $forcedLanguage] )`

* ez_field_value

  _Since 5.2_

  `Field|null = ez_field_value( Content $content, string $fieldDefIdentifier[, string $forcedLanguage] )`

* ez_is_field_empty

  _Since 5.2, starting 5.3 it optionally supports Field object as second argument_

  `bool = ez_is_field_empty( Content $content, string|Field $fieldDefIdentifier[, string $forcedLanguage] )`

* ez_field_name

  _Since 5.4_

  `string|null = ez_field_name( Content|ContentInfo $content, string $fieldDefIdentifier[, string $forcedLanguage] )`

* ez_field_description

  _Since 5.4_

  `string|null = ez_field_description( Content|ContentInfo $content, string $fieldDefIdentifier[, string $forcedLanguage] )`

* ez_trans_prop

  _Since 5.4_

  `string|null = ez_trans_prop( \eZ\Publish\API\Repository\Values\ValueObject $object, string $property[, string $forcedLanguage] )`

  A low level generic helper, will check if `{$property}s` property or `get{$property}` method exists on provided object
  Using one or the other will attempt to get a value using either forced language _or_ SiteAccess language list, with
  _main language_ fallback if no other language had a value.

  _Main language_ is attempted to be applied in the following way for Value objects that support this:
   * property: Use mainLanguageCode property if it exists, but not if alwaysAvailable exists and is true
   *  method: Provide $langueage = null as argument to method, depends on logic of ValueObject if this gives a value or not

  Example: `ez_trans_prop( versionInfo, 'name' )` will provide the same result as using `ez_content_name( content )`, in
      both cases `VersionInfo->getName( $lang )` is internally used in prioritized language order, with main language fallback.
      
* ez_field

  _Since 6.1_

  `Field|null = ez_field( Content $content, string $fieldDefIdentifier[, string $forcedLanguage] )`

  Just like ez_field_value except it returns the whole translated Field.


## Rendering helpers

Group of helpers to deal with rendering, mainly of field values and it's settings.

* ez_render_field

  _Since 5.1_

  `ez_render_field( Content $content, string $fieldDefIdentifier[, array $params ] )`

* ez_render_fielddefinition_settings

  _Since 5.2_

  `ez_render_fielddefinition_settings( \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $definition )`
