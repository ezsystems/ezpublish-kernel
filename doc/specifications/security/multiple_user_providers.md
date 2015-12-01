# Using multiple user providers for authentication

## Description

Symfony provides native support for [multiple user providers](http://symfony.com/doc/2.3/book/security.html#using-multiple-user-providers).
This makes it easy to integrate any kind of login handlers, including SSO and existing 3rd party bundles
(e.g. [FR3DLdapBundle](https://github.com/Maks3w/FR3DLdapBundle), [HWIOauthBundle](https://github.com/hwi/HWIOAuthBundle),
[FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle), [BeSimpleSsoAuthBundle](http://github.com/BeSimple/BeSimpleSsoAuthBundle)...).

However, to be able to use *external* user providers with eZ, a valid eZ user needs to be injected in the repository.
This is mainly for the kernel to be able to manage content related permissions (but not limited to).

Depending on your context, you will either want to create an eZ user `on-the-fly`, return an existing user, or even
always use a generic user.

## Solution

Whenever a *external* user is matched (i.e. that does not come from eZ repository, like coming from LDAP),
eZ kernel fires an `MVCEvents::INTERACTIVE_LOGIN` event. Every service listening to this event will receive a
`eZ\Publish\Core\MVC\Symfony\Event\InteractiveLoginEvent` object which contains the original security token (that
holds the matched user) and the request.

It's then up to the listener to retrieve an eZ user from repository and assign it back to the event object.
This user will be injected in the repository and used for the rest of the request.

> If no eZ user is returned, the anonymous user will then be used.

### User exposed and security token
When a *external* user is matched, a different token will be injected in the security context, the `InteractiveLoginToken`.
This token holds a `UserWrapped` instance which contains the originally matched user and the *API user* (the one
from the eZ repository).

Note that the *API user* is mainly used for permission checks against the repository and thus stays *under the hood*.

### Customizing the user class
It is possible to customize the user class used by extending `ezpublish.security.login_listener` service,
which defaults to `eZ\Publish\Core\MVC\Symfony\Security\EventListener\SecurityListener`.

You can override `getUser()` to return whatever user class you want, as long as it implements
`eZ\Publish\Core\MVC\Symfony\Security\UserInterface`.


## Example

Here is a very simple example using the in-memory user provider.

*app/config/security.yml*
```yaml
security:
    providers:
        # Chaining in_memory and ezpublish user providers
        chain_provider:
            chain:
                providers: [in_memory, ezpublish]
        ezpublish:
            id: ezpublish.security.user_provider
        in_memory:
            memory:
                users:
                    # You will then be able to login with username "user" and password "userpass"
                    user:  { password: userpass, roles: [ 'ROLE_USER' ] }
    # The "in memory" provider requires an encoder for Symfony\Component\Security\Core\User\User
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext
```

**Implementing the listener**

*services.yml in AcmeTestBundle*
```yaml
parameters:
    acme_test.interactive_event_listener.class: Acme\TestBundle\EventListener\InteractiveLoginListener

services:
    acme_test.interactive_event_listener:
        class: %acme_test.interactive_event_listener.class%
        arguments: [@ezpublish.api.service.user]
        tags:
            - { name: kernel.event_subscriber }
```

*InteractiveLoginListener*
```php
<?php
namespace Acme\TestBundle\EventListener;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\MVC\Symfony\Event\InteractiveLoginEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InteractiveLoginListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    public function __construct( UserService $userService )
    {
        $this->userService = $userService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        );
    }

    public function onInteractiveLogin( InteractiveLoginEvent $event )
    {
        // We just load a generic user and assign it back to the event.
        // You may want to create users here, or even load predefined users depending on your own rules.
        $event->setApiUser( $this->userService->loadUserByLogin( 'lolautruche' ) );
    }
}
```
