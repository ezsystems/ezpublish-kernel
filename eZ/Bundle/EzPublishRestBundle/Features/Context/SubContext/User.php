<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use PHPUnit\Framework\Assert as Assertion;

/**
 * @method \eZ\Publish\API\Repository\Repository getRepository()
 */
trait User
{
    /**
     * @Given /^there is a user with the login "([^"]*)"$/
     */
    public function thereIsAUserWithTheLogin($login)
    {
        Assertion::assertInstanceOf(
            'eZ\Publish\API\Repository\Values\User\User',
            $this->getRepository()->getUserService()->loadUserByLogin($login)
        );
    }

    /**
     * @Given /^there isn't a user with the login "([^"]*)"$/
     */
    public function thereIsNotAUserWithTheLogin($login)
    {
        try {
            $this->getRepository()->getUserService()->loadUserByLogin($login);
        } catch (NotFoundException $e) {
            return;
        }
        throw new BadStateException('login', "A user with the login $login exists");
    }

    /**
     * Verifies that there is at least one user with a given email address.
     *
     * @Given /^there is a at least one user with the email "([^"]*)"$/
     * @Given /^there is a user with the email "([^"]*)"$/
     */
    public function thereIsAUserWithTheEmail($email)
    {
        $users = $this->getRepository()->getUserService()->loadUsersByEmail($email);

        Assertion::assertGreaterThan(0, count($users), "No user was found with the email '$email'");
        Assertion::assertInstanceOf('eZ\Publish\API\Repository\Values\User\User', $users[0]);
    }

    /**
     * Verifies that there is at least one user with a given email address.
     *
     * @Given /^there isn't a user with the email "([^"]*)"$/
     * @Given /^there are no users with the email "([^"]*)"$/
     */
    public function thereAreNoUsersWithTheEmail($email)
    {
        $users = $this->getRepository()->getUserService()->loadUsersByEmail($email);
        Assertion::assertEquals(0, count($users), "Users with the email '$email' exist");
    }

    /**
     * @Given /^response contains only the user with the login "([^"]*)"$/
     */
    public function responseContainsOnlyTheUserWithTheLogin($login)
    {
        $userList = $this->getResponseObject();

        Assertion::assertEquals(
            1,
            count($userList),
            'UserList was expected to contain one user only'
        );

        Assertion::assertInstanceOf(
            'eZ\Publish\API\Repository\Values\User\User',
            $userList[0],
            'UserList[0] is not a user'
        );

        Assertion::assertEquals(
            $login,
            $userList[0]->login,
            "UserList was expected to contain the user with login '$login'"
        );
    }

    /**
     * @Given /^response contains only the user with the email "([^"]*)"$/
     */
    public function responseContainsOnlyUsersWithTheEmail($email)
    {
        $userList = $this->getResponseObject();

        Assertion::assertGreaterThan(
            0,
            count($userList),
            'UserList was expected to contain one user only'
        );

        foreach ($userList as $user) {
            Assertion::assertInstanceOf(
                'eZ\Publish\API\Repository\Values\User\User',
                $user,
                'Non User found in the response'
            );

            Assertion::assertEquals(
                $email,
                $user->email,
                "UserList was expected to contain only users with email '$email'"
            );
        }
    }

    /**
     * @Given /^that I can't view users$/
     */
    public function thatICannotViewUsers()
    {
        $this->usePermissionsOfRole('anonymous');
    }
}
