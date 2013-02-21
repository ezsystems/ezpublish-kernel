eZ Publish: Permissions, Design
===============================

API
---


### Model API

This part has already been defined, and value objects can be found here:
* https://github.com/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/User

For the Service API's that deal with these objects, look here:
* https://github.com/ezsystems/ezpublish-kernel/blob/master/eZ/Publish/API/Repository/UserService.php
* https://github.com/ezsystems/ezpublish-kernel/blob/master/eZ/Publish/API/Repository/RoleService.php


### Authorization API

#### Prior approach

Prior work last year consisted of a high level [Repository->canUser()][canUser] api, that
underneath consumed two api's:
* [User->hasAccessTo()][hasAccessTo]
* Model->definition(), examples:
    * [Content->definition()][contentDefinition]
    * [User->definition()][userDefinition]

definition() was where the heavy lifting was done, this consisted of the following
closures pr limitation:
* 'compare' to perform permission check against live objects, as needed
  when storing new objects.
* 'query' cable of generating criterion for filtering search results and (child)
  list operations. The systems also used this criterion inverse for checking if
  user has access to delete or move a subtree.


#### Updated approach

Prior approach had downside in that it used closures and was hard to extend.
So in updated approach these permission check functions are moved to a
Limitation SPI interface (since extensions should be able to add their own).

The SPI interface:

```php
namespace eZ\Publish\SPI\Limitation;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation as LimitationValue;
use eZ\Publish\API\Repository\Repository;

/**
 * This interface represent the Limitation Type
 */
interface Type
{
    /**
     * Constants for valueSchema() return values
     *
     * Used in cases where a certain value is accepted but the options are to many to return as a hash of options.
     * GUI should typically present option to browse content tree to select limitation value(s).
     */
    const VALUE_SCHEMA_LOCATION_ID = 1;
    const VALUE_SCHEMA_LOCATION_PATH = 2;

    /**
     * Accepts a Limitation value
     *
     * Makes sure LimitationValue object is of correct type and that ->limitationValues
     * is valid according to valueSchema().
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return boolean
     */
    public function acceptValue( LimitationValue $limitationValue, Repository $repository );

    /**
     * Create the Limitation Value
     *
     * The is the api to create values as Limitation type needs value knowledge anyway in acceptValue,
     * the reverse relation is provided by means of identifier lookup (Value has identifier, and so does RoleService).
     *
     * @param mixed[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue( array $limitationValues );

    /**
     * Evaluate permission against content & target(placement/parent/assignment)
     *
     * NOTE: Repository is provided because not everything is available via the value object(s),
     * but use of repository in limitation functions should be avoided for performance reasons
     * if possible, especially when using un-cached parts of the api.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     *         Example: If LimitationValue is instance of ContentTypeLimitationValue, and Type is SectionLimitationType.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *         Example if OwnerLimitationValue->limitationValues[0] is not one of: [ 1,  2 ]
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject $target The location, parent or "assignment" value object
     *
     * @return boolean
     */
    public function evaluate( LimitationValue $value, Repository $repository, ValueObject $object, ValueObject $target = null );

    /**
     * Return Criterion for use in find() query
     *
     * NOTE: Repository is provided because not everything is available via the limitation value,
     * but use of repository in limitation functions should be avoided for performance reasons
     * if possible, especially when using un-cached parts of the api.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     */
    public function getCriterion( LimitationValue $value, Repository $repository );

    /**
     * Return info on valid $limitationValues
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_* constants.
     *                     Note: The hash might be an instance of Traversable, and not a native php array.
     */
    public function valueSchema( Repository $repository );
}
```


Methods on RoleService needs to be added to provide access to these Types, as GUI's will need to at least have access
to valueSchema() to provide admin interfaces where policies can be created / updated.
The added RoleService method is to get them by identifier or list based on module / function:

```php
    /**
     * Returns the LimitationType registered with the given identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Limitation\Type
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if there is no LimitationType with $identifier
     */
    public function getLimitationType( $identifier );

    /**
     * Returns the LimitationType's assigned to a given module/function
     *
     * Typically used for:
     *  - Internal validation limitation value use on Policies
     *  - Role admin gui for editing policy limitations incl list limitation options via valueSchema()
     *
     * @param string $module Legacy name of "controller", it's a unique identifier like "content"
     * @param string $function Legacy name of a controller "action", it's a unique within the controller like "read"
     *
     * @return \eZ\Publish\SPI\Limitation\Type[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If module/function to limitation type mapping
     *                                                                 refers to a non existing identifier.
     */
    public function getLimitationTypesByModuleFunction( $module, $function );
```



### Authentication API

The API needs two ways user can be authenticated:
* Using login/email and password: [UserService->loadUserByCredentials()][login]
* Using user reference for session use: <@todo: TBD>



Implementation notes
--------------------

### Extensibility

This is archived by means of using the dependency injection container system to map
up the different Limitation Types mapped by identifier, and a mapping of module functions
to Limitation identifiers to be able to validate limitations values used on module functions.

This means identifier needs to be unique!



References
----------

[canUser]:           https://github.com/ezsystems/ezpublish-kernel/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/Base/Repository.php#L122
[contentDefinition]: https://github.com/ezsystems/ezpublish-kernel/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/Content/Concrete.php#L235
[userDefinition]:    https://github.com/ezsystems/ezpublish-kernel/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/User/Concrete.php#L96
[hasAccessTo]:       https://github.com/ezsystems/ezpublish-kernel/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/User/Concrete.php#L162
[login]:             https://github.com/ezsystems/ezpublish-kernel/blob/master/eZ/Publish/API/Repository/UserService.php#L142
