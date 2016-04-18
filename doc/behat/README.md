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
```
