<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Exception;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
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
    /** @var \Symfony\Component\DependencyInjection\Reference[] */
    private $queryTypeRefs = [];

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.query_type.registry')) {
            return;
        }

        $queryTypesRefs = [];

        // array of QueryType classes. Used to prevent double handling between services & convention definitions.
        $queryTypesClasses = [];

        // tagged query types
        foreach ($container->findTaggedServiceIds('ezpublish.query_type') as $taggedServiceId => $tags) {
            $queryTypeDefinition = $container->getDefinition($taggedServiceId);
            $queryTypeClassName = $queryTypeDefinition->getClass();

            for ($i = 0, $count = count($tags); $i < $count; ++$i) {
                $queryTypesRefs[] = new Reference($taggedServiceId);
                $queryTypesClasses[$queryTypeClassName] = true;
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

                $conventionQueryTypeDefs = [];
                $bundleQueryTypeNamespace = substr($bundleClass, 0, strrpos($bundleClass, '\\') + 1) . 'QueryType';
                foreach (glob($bundleQueryTypesDir . DIRECTORY_SEPARATOR . '*QueryType.php') as $queryTypeFilePath) {
                    $queryTypeFileName = basename($queryTypeFilePath, '.php');
                    $queryTypeClassName = $bundleQueryTypeNamespace . '\\' . $queryTypeFileName;
                    if (isset($queryTypesClasses[$queryTypeClassName])) {
                        continue;
                    }
                    if (!class_exists($queryTypeClassName)) {
                        throw new Exception("Expected $queryTypeClassName to be defined in $queryTypeFilePath");
                    }

                    $this->checkInterface($queryTypeClassName);

                    $serviceId = 'ezpublish.query_type.convention.' . strtolower($bundleName) . '_' . strtolower($queryTypeFileName);
                    $queryTypeDefinition = new Definition($queryTypeClassName);
                    $conventionQueryTypeDefs[$serviceId] = $queryTypeDefinition;
                    $queryTypesRefs[] = new Reference($serviceId);
                }
                $container->addDefinitions($conventionQueryTypeDefs);
            }
        }

        $registryDef = $container->getDefinition('ezpublish.query_type.registry');
        $registryDef->addMethodCall('addQueryTypes', [$queryTypesRefs]);
    }

    /**
     * Checks that $queryTypeClassName implements the QueryType interface.
     *
     * @param string $queryTypeClassName
     *
     * @throws InvalidArgumentException
     */
    private function checkInterface($queryTypeClassName)
    {
        $queryTypeReflectionClass = new ReflectionClass($queryTypeClassName);
        if (!$queryTypeReflectionClass->implementsInterface('eZ\Publish\Core\QueryType\QueryType')) {
            throw new InvalidArgumentException(
                "QueryTypeClass $queryTypeClassName",
                'needs to implement eZ\Publish\Core\QueryType\QueryType'
            );
        }
    }
}
