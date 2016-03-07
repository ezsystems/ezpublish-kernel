##### Accessing eZ Platform / Kernel services
In order to easily access container services (such as ContentService, SearchService, etc), [EzBehatExtension](Context/Argument/AnnotationArgumentResolver.php)
can parse annotations in Behat Contexts and use the Argument Resolver to inject the defined services into the constructor, using the following syntax:

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
