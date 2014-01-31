<?php
/**
 * File containing the LegacyDbHandlerFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class LegacyDbHandlerFactory
{

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    public function __construct( ConfigResolverInterface $resolver )
    {
        $this->configResolver = $resolver;
    }

    /**
     * Builds the DB handler used by the legacy storage engine.
     *
     * @throws \RuntimeException
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    public function buildLegacyDbHandler()
    {
        $dbParams = $this->configResolver->getParameter( 'database.params' );

        try
        {
            $handler = EzcDbHandler::create( $dbParams );
        }
        catch ( \PDOException $e )
        {
            $msg =
                "Unable to create Legacy DB handler {$dbParams['type']}:host={$dbParams['host']};database={$dbParams['database']}."
                . " Please check your database settings in ezpublish_*.yml.";

            throw new \RuntimeException( $msg, null, $e );
        }

        return $handler;
    }
}
