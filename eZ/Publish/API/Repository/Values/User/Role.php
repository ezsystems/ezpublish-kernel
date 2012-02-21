<?php

namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a role
 * 
 * @property-read mixed $id the internal id of the role
 * @property-read string $identifier the identifier of the role
 * @property-read string $mainLanguageCode 5.0, the main language of the role names and description used for fallback.
 */
abstract class Role extends ValueObject
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    protected $id;

     /**
     * Readable string identifier of a role
     * in 4.x. this is mapped to the role name
     *
     * @var string
     */
    protected $identifier;
    
   /**
     * the main language code
     *
     * @since 5.0
     *
     * @var string
     */
    protected $mainLanguageCode;

    /**
     *
     * This method returns the human readable name in all provided languages
     * of the role
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @since 5.0
     *
     * @return string[]
     */
    abstract public function getNames();

    /**
     * this method returns the name of the role in the given language
     *
     * @since 5.0
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    abstract public function getName( $languageCode );

    /**
     * This method returns the human readable description of the role
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @since 5.0
     *
     * @return string[]
     */
    abstract public function getDescriptions();

    /**
     * this method returns the name of the role in the given language
     *
     * @since 5.0
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    abstract public function getDescription( $languageCode );
    

    /**
     * returns the list of policies of this role
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Policy}
     */
    abstract public function getPolicies();
}
