##### Accessing eZ Platform / Kernel services
In order to easily access container services (such as ContentService, SearchService, etc),
[EzBehatExtension](/eZ/Bundle/PlatformBehatBundle/Context/Argument/AnnotationArgumentResolver.php)
can parse annotations in Behat Contexts and use the Argument Resolver to inject the defined services
into the constructor, using the following syntax:

``` php
/**
 * @injectService $service1 @ezpublish.api.some.service
 * @injectService $service2 @ezpublish.api.another.service
 */
 public function __constructor(SomeService $service1, AnotherService $service2)
 {
    ...
 }
```

#### Deprecation notices
Deprecation notices cause behat tests to fail and output to the console the notices, ruinning the behat output,so in
order to get rid of this and run tests that use deprecated code the
[DeprecationNoticeSupressor](/eZ/Bundle/PlatformBehatBundle/Context/SubContext/DeprecationNoticeSupressor.php) should
be included in the respective Context. Ex:

``` php
use EzSystems\PlatformBehatBundle\Context\SubContext\DeprecationNoticeSupressor;

class MyContextUsesDeprecatedCode implements Context
{
    use DeprecationNoticeSupressor;
...
```

##### Using the Repository
To ease the usage and maintainability of the repository dependent
Behat Contexts a trait called `RespositoryContext` was created.
This trait includes all the methods, variables related directly
with the repository. To use this all that is needed is to use it
in your own Contexts. Ex:

``` php
...
/**
 * My very own Behat Context
 */
class MyContext implements Context
{
    use RepositoryContext;
...
```
