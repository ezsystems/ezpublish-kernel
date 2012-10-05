<?php
/**
 * NewUserCreateStructSignal class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Signal\UserService;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * NewUserCreateStructSignal class
 * @package eZ\Publish\Core\SignalSlot\Signal\UserService
 */
class NewUserCreateStructSignal extends Signal
{
    /**
     * Login
     *
     * @var mixed
     */
    public $login;

    /**
     * Email
     *
     * @var mixed
     */
    public $email;

    /**
     * Password
     *
     * @var mixed
     */
    public $password;

    /**
     * MainLanguageCode
     *
     * @var mixed
     */
    public $mainLanguageCode;

    /**
     * ContentType
     *
     * @var mixed
     */
    public $contentType;

}

