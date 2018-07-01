<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Exception;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Processes services tagged as ezpublish.query_type, and registers them with ezpublish.query_type.registry.
 */
class QueryTypePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.query_type.registry')) {
            return;
        }

        $queryTypes = [];
        $queryTypesClasses = [];

        // tagged query types
        $taggedServiceIds = $container->findTaggedServiceIds('ezpublish.query_type');
        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            $queryTypeDefinition = $container->getDefinition($taggedServiceId);
            $queryTypeClass = $container->getParameterBag()->resolveValue($queryTypeDefinition->getClass());

            $queryTypesClasses[$queryTypeClass] = [];

            for ($i = 0, $count = count($tags); $i < $count; ++$i) {
                $name = isset($tags[$i]['alias']) ? $tags[$i]['alias'] : $queryTypeClass::getName();
                $queryTypes[$name] = new Reference($taggedServiceId);
                $queryTypesClasses[$queryTypeClass][$name] = true;
            }
        }

        // named by convention query types
        if ($container->hasParameter('kernel.bundles')) {
            foreach ($container->getParameter('kernel.bundles') as $bundleName => $bundleClass) {
                $bundleReflectionClass = new ReflectionClass($bundleClass);
                $bundleDir = dirname($bundleReflectionClass->getFileName());

                $bundleQueryTypesDir = $bundleDir . DIRECTORY_SEPARATOR . 'QueryType';

                if (!is_dir($bundleQueryTypesDir)) {
                    continue;
                }

                $queryTypeServices = [];
                $bundleQueryTypeNamespace = substr($bundleClass, 0, strrpos($bundleClass, '\\') + 1) . 'QueryType';
                foreach (glob($bundleQueryTypesDir . DIRECTORY_SEPARATOR . '*QueryType.php') as $queryTypeFilePath) {
                    $queryTypeFileName = basename($queryTypeFilePath, '.php');
                    $queryTypeClassName = $bundleQueryTypeNamespace . '\\' . $queryTypeFileName;
                    if (!class_exists($queryTypeClassName)) {
                        throw new Exception("Expected $queryTypeClassName to be defined in $queryTypeFilePath");
                    }

                    $queryTypeName = $queryTypeClassName::getName();

                    // skip if the class was already registered as a tagged service with the same name
                    if (isset($queryTypesClasses[$queryTypeClassName][$queryTypeName])) {
                        continue;
                    }

                    $queryTypeReflectionClass = new ReflectionClass($queryTypeClassName);
                    if (!$queryTypeReflectionClass->implementsInterface('eZ\Publish\Core\QueryType\QueryType')) {
                        throw new Exception("$queryTypeClassName needs to implement eZ\\Publish\\Core\\QueryType\\QueryType");
                    }

                    $serviceId = 'ezpublish.query_type.convention.' . strtolower($bundleName) . '_' . strtolower($queryTypeFileName);
                    $queryTypeServices[$serviceId] = new Definition($queryTypeClassName);

                    $queryTypes[$queryTypeName] = new Reference($serviceId);
                }

                $container->addDefinitions($queryTypeServices);
            }
        }

        $aggregatorDefinition = $container->getDefinition('ezpublish.query_type.registry');
        $aggregatorDefinition->addMethodCall('addQueryTypes', [$queryTypes]);
    }
}
