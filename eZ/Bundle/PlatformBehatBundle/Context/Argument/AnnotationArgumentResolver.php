<?php

namespace EzSystems\PlatformBehatBundle\Context\Argument;

use Behat\Behat\Context\Argument\ArgumentResolver;
use ReflectionClass;

/**
 * Behat Context Argument Resolver.
 */
class AnnotationArgumentResolver implements ArgumentResolver
{
    /**
     * Service annotation tag.
     */
    const SERVICE_DOC_TAG = 'injectService';

    /**
     * Resolve service arguments for Behat Context constructor thru annotation.
     * Symfony2Extension ArgumentResoler will convert service names to actual instances.
     *
     * @param ReflectionClass $classReflection
     * @param array $arguments
     */
    public function resolveArguments(ReflectionClass $classReflection, array $arguments = [])
    {
        $injArguments = $this->parseAnnotations(
            $this->getMethodAnnotations($classReflection)
        );

        if (!empty($injArguments)) {
            $arguments = [];
            foreach ($injArguments as $name => $service) {
                $arguments[$name] = $service;
            }
        }

        return $arguments;
    }

    /**
     * Returns a array with the method annotations.
     *
     * @return array array annotations
     */
    private function getMethodAnnotations($refClass, $method = '__construct')
    {
        if ($refClass->hasMethod($method)) {
            $refMethod = $refClass->getMethod($method);
            preg_match_all('#@(.*?)\n#s', $refMethod->getDocComment(), $matches);

            return $matches[1];
        } else {
            return [];
        }
    }

    /**
     * Returns an array with the method arguments service requirements,
     * if the methods use the service Annotation.
     *
     * @return array array of methods and their service dependencies
     */
    private function parseAnnotations($annotations)
    {
        // parse array from (numeric key => 'annotation <value>') to (annotation => value)
        $methodServices = [];
        foreach ($annotations as $annotation) {
            if (!preg_match('/^(\w+)\s+\$(\w+)\s+([\w\.\\\@\%]+)/', $annotation, $matches)) {
                continue;
            }

            array_shift($matches);
            $tag = array_shift($matches);
            if ($tag == self::SERVICE_DOC_TAG) {
                list($argument, $service) = $matches;
                $methodServices[$argument] = $service;
            }
        }

        return $methodServices;
    }
}
