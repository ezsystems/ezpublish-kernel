<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

use ezp\PublicAPI\Values\Content\VersionInfo;

/**
 * 
 * this class represents a version including metadata and content
 * 
 * @property-read ContentInfo $contentInfo convenience getter for $versionInfo->ccntentInfo
 * @property-read int $contentId convenience getter for retrieving the contentId: $versionInfo->contentInfo->contentId
 * @property-read VersionInfo $versionInfo calls getVersionInfo()
 * @property-read array $fields access fields 
 * @property-read array $relations calls getRelations()
 *
 */
abstract class Version extends ValueObject
{
	/**
	 * returns the VersionInfo for this version
	 *
	 * @return VersionInfo 
	 */
	public abstract function getVersionInfo();

	/**
	 * returns a field value for the given value
	 * $version->fields[$fieldDefId][$languageCode] is an equivalent call
	 * if no language is given on a translatable field this method returns 
	 * the value of the initial language of the version if present, otherwise null.
	 * On non translatable fields this method ignores the languageCode parameter.
	 * @param string $fieldDefId
	 * @param string $languageCode
	 * @return mixed a primitive type or a field type Value object depending on the field type.
	 */
	public abstract function getFieldValue($fieldDefId,$languageCode = null);
	
	
	/**
	 * 
	 * returns the outgoing relations 
	 * @return array an array of {@link Relation}
	 */
    public abstract function getRelations();
    
    /**
     * 
     * This method returns the complete fields collection
     * @return array an array of {@link Field}
     */
    public abstract function getFields();
    
    /**
     * This method returns the fields for a given language
     * @param string $languageCode
     * @return array an array of {@link Field}
     */ 
    public abstract function getFieldsByLanguage($languageCode);
}
?>
