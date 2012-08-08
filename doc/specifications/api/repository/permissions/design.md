eZ Publish: Permissions, Design
===============================

API
---


### Model API

This part has already been defined, and value objects can be found here:
https://github.com/ezsystems/ezp-next/tree/master/eZ/Publish/API/Repository/Values/User

For the Service API that deal with these objects, look here:
https://github.com/ezsystems/ezp-next/blob/master/eZ/Publish/API/Repository/UserService.php


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

The following functions should be added to a SPI for Limitations:
´´´php
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
     * @return bool
     */
    public function acceptValue( LimitationValue $limitationValue, Repository $repository );

    /**
     * Create the Limitation Value
     *
     * @param mixed[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue( array $limitationValues );

    /**
     * Evaluate permission against content and placement
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject $placement In 'create' limitations this is the parent
     *
     * @return bool
     */
    public function evaluate( LimitationValue $value, Repository $repository, ValueObject $object, ValueObject $placement = null );

    /**
     * Return Criterion for use in find() query
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
     */
    public function valueSchema( Repository $repository );
}
´´´
Note: Access to Repository in evaluate() and getCriterion() is needed as not everything
      is available via the object graph, but use of repository in limitations functions
      should be avoided for performance reasons, especially when using un-cached parts
      of the api.

Note2: buildValue() exists so that dependency injection system only have to know about the Limitation "Type".
       Other functions like acceptValue() [and evaluate()] needs to know about the Limitation Value class anyway.
       Hence this class organization is a simplified version of the one used on FiledTypes, which should be familiar.


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

[canUser]:           https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/Base/Repository.php#L122
[contentDefinition]: https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/Content/Concrete.php#L235
[userDefinition]:    https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/User/Concrete.php#L96
[hasAccessTo]:       https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/User/Concrete.php#L162
[login]:             https://github.com/ezsystems/ezp-next/blob/master/eZ/Publish/API/Repository/UserService.php#L142