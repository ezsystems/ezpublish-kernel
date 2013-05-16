<?php
/**
 * File containing the user AuthenticationController class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\User;

use eZ\Publish\Core\MVC\Symfony\Controller\Controller,
    eZ\Publish\Core\MVC\Symfony\Security\User\HashGenerator,
    Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{
    private $userHashGenerator;

    public function __construct( HashGenerator $userHashGenerator )
    {
        $this->userHashGenerator = $userHashGenerator;
    }

    public function getUserHash()
    {
        return new Response( $this->userHashGenerator->generate() );
    }
}
