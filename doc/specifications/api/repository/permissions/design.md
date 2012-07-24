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
So in updated approach these permission check functions are moved to Limitation
objects.

The following functions should be added to API for Content limitations:

    /**
     * Evaluate permission against live objects
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject $placement In 'create' limitations; this is parent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If limitation data is inconsistent
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If wrong Value objects are used
     * @return bool
     */
    abstract public function evaluate( Repository $repository, ValueObject $object, ValueObject $placement = null );

    /**
     * Return Criterion for use in find() query
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If limitation data is inconsistent
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     */
    abstract public function getCriterion( Repository $repository );

    /**
     * Return array with possible options for limitations
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If limitation data is inconsistent
     * @return array A hash where key is value for use in limitation values, and value is human readable
     *               name for the option in question.
     */
    abstract public function options( Repository $repository );

Access to Repository in evaluate() and getCriterion() is needed as not everything
is available via the object graph, but use of repository in limitations functions
should be avoided for performance reasons, especially when using un-cached parts
of the api.

@todo ->limitationValues needs to be validated using ->options() on store and update.


### Authentication API

The API needs two ways user can be authenticated:
* Using login/email and password: [UserService->loadUserByCredentials()][login]
* Using s user reference for session use: <TBD>



Implementation
--------------

### Extensibility

This can be archived by means of using the dependency injection container system to map
up the different limitations and which module functions they should be used in.



References
----------

[canUser]:           https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/Base/Repository.php#L122
[contentDefinition]: https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/Content/Concrete.php#L235
[userDefinition]:    https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/User/Concrete.php#L96
[hasAccessTo]:       https://github.com/ezsystems/ezp-next/blob/9e14c6b3133a2585c103376555849c5fcd8591d2/ezp/User/Concrete.php#L162
[login]:             https://github.com/ezsystems/ezp-next/blob/master/eZ/Publish/API/Repository/UserService.php#L142