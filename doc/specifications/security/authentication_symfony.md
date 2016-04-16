# Authentication through Symfony

## Description

Prior to eZ Publish 5.3, authentication was made through legacy stack only, using the venerable `user/login` module,
with the help of a `PreAuthenticatedProvider`.

Main drawback was that it was impossible to use full power of Symfony security component, like the usage of multiple
user providers.

## Solution

Native and universal `form_login` has been used, in conjunction to an extended `DaoAuthenticationProvider` (DAO stands
for *Data Access Object*), the `RepositoryAuthenticationProvider`.
Native behavior of `DaoAuthenticationProvider` has been preserved, making it possible to still use it for pure Symfony applications.

### Security controller
A `SecurityController` has been introduced to manage all security related actions and is thus used to display login form.
It is pretty straight forward and follows all standards explained in [Symfony security documentation](http://symfony.com/doc/2.3/book/security.html#using-a-traditional-login-form).

Base template used is `EzPublishCoreBundle:Security:login.html.twig` and stands as follows:

```jinja
{% extends layout %}

{% block content %}
    {% block login_content %}
        {% if error %}
            <div>{{ error.message|trans }}</div>
        {% endif %}

        <form action="{{ path( 'login_check' ) }}" method="post">
        {% block login_fields %}
            <label for="username">{{ 'Username:'|trans }}</label>
            <input type="text" id="username" name="_username" value="{{ last_username }}" />

            <label for="password">{{ 'Password:'|trans }}</label>
            <input type="password" id="password" name="_password" />

            <input type="hidden" name="_csrf_token" value="{{ csrf_token }}" />

            {#
                If you want to control the URL the user
                is redirected to on success (more details below)
                <input type="hidden" name="_target_path" value="/account" />
            #}

            <button type="submit">{{ 'Login'|trans }}</button>
        {% endblock %}
        </form>
    {% endblock %}
{% endblock %}
```

The layout used by default is `%ezsettings.default.pagelayout%` but can be configured easily
as well as the login template:

*ezpublish.yml*
```yaml
ezpublish:
    system:
        my_siteaccess:
            user:
                layout: "AcmeTestBundle::layout.html.twig"
                login_template: "AcmeTestBundle:User:login.html.twig"
```

#### Redirection after login
By default, Symfony redirects to the [URI configured in `security.yml` as `default_target_path`](http://symfony.com/doc/2.3/reference/configuration/security.html).
If not set, it will default to `/`.

This setting can be set by SiteAccess, via [`default_page` setting](../misc/default_page.md).

### Configuration

To use Symfony authentication with eZ Publish, the configuration goes as follows:

*app/config/security.yml*
```yaml
security:
    firewalls:
        ezpublish_front:
            pattern: ^/
            anonymous: ~
            form_login:
                require_previous_session: false
            logout: ~
```

*app/config/routing.yml*
```yaml
login:
    path:   /login
    defaults:  { _controller: ezpublish.security.controller:loginAction }
login_check:
    path:   /login_check
logout:
    path:   /logout
```

> **Note:**
> You can fully customize the routes and/or the controller used for login.
> However, ensure to match `login_path`, `check_path` and `logout.path` from `security.yml`.
>
> See [security configuration reference](http://symfony.com/doc/2.3/reference/configuration/security.html)
> and [standard login form documentation](http://symfony.com/doc/2.3/book/security.html#using-a-traditional-login-form)

### Remember me
It is possible to use the `remember_me` functionality. For this you can refer to the
[Symfony cookbook on this topic](http://symfony.com/doc/2.3/cookbook/security/remember_me.html).

If you want to use this feature, you must at least extend the login template in order to add the required checkbox:

```jinja
{# your_login_template.html.twig #}
{% extends "EzPublishCoreBundle:Security:login.html.twig" %}

{% block login_fields %}
    {{ parent() }}
    <input type="checkbox" id="remember_me" name="_remember_me" checked />
    <label for="remember_me">Keep me logged in</label>
{% endblock %}
```

### Login Handlers / SSO
Symfony provides native support for [multiple user providers](https://github.com/ezsystems/ezpublish-kernel/pull/symfony.com/doc/2.3/book/security.html#using-multiple-user-providers).
This makes it easy to integrate any kind of login handlers, including SSO and existing 3rd party bundles
(e.g. [FR3DLdapBundle](https://github.com/Maks3w/FR3DLdapBundle), [HWIOauthBundle](https://github.com/hwi/HWIOAuthBundle),
[FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle), [BeSimpleSsoAuthBundle](http://github.com/BeSimple/BeSimpleSsoAuthBundle)...).

Further explanation can be found in the [multiple user providers specification](multiple_user_providers.md).

### Integration with legacy

* When **not** in legacy mode, legacy `user/login` and `user/logout` views are deactivated.
* Authenticated user is injected in legacy kernel.

## Upgrade notes
* In `app/config/security.yml`, you must remove `ezpublish: true` from `ezpublish_front` firewall.
* In `app/config/routing.yml`, you must add `login`, `login_check` and `logout` routes
  (see above in [Configuration][])
* In your templates, change your links pointing to `/user/login` and `/user/logout` to appropriate login/login_check/logout routes:

*Before*
```jinja
<a href="{{ path( 'ez_legacy', {'module_uri': '/user/login'} ) }}">Login</a>

<form action="{{ path( 'ez_legacy', {'module_uri': '/user/login'} ) }}" method="post">

<a href="{{ path( 'ez_legacy', {'module_uri': '/user/logout'} ) }}">Logout</a>
```

*After*
```jinja
<a href="{{ path( 'login' ) }}">Login</a>

<form action="{{ path( 'login_check' ) }}" method="post">

<a href="{{ path( 'logout' ) }}">Logout</a>
```

* Anonymous state is not checked through presence of `is_logged_in` cookie any more.
  Therefore, when using Varnish, you must change the following in your VCL file:

*Before*
```
# ez_user_hash sub-routine
if (req.http.Cookie !~ "is_logged_in=" ) {
    # User don't have "is_logged_in" cookie => Set a hardcoded anonymous hash
    set req.http.X-User-Hash = "38015b703d82206ebc01d17a39c727e5";
}
```

*After*
```
# ez_user_hash sub-routine
if (req.http.Cookie !~ "eZSESSID" ) {
    # User don't have session cookie => Set a hardcoded anonymous hash
    set req.http.X-User-Hash = "38015b703d82206ebc01d17a39c727e5";
}
```
