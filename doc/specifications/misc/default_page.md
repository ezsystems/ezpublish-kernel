# Default page

## Description
Default page is the default page to show or redirect to.

If set, it will be used for default redirection after user login, overriding Symfony's `default_target_path`, giving
the opportunity to configure it by SiteAccess.

## Usage
Default page can easily be configured by SiteAccess:

```yaml
ezpublish:
    system:
        ezdemo_site:
            default_page: "/Getting-Started"

        ezdemo_site_admin:
            # For admin, redirect to dashboard after login.
            default_page: "/content/dashboard"
```

## Order of precedence
This setting **does not change anything to Symfony behavior** regarding redirection after login. If set, it will only
substitute the value set for `default_target_path`. It is therefore still possible to specify a custom target path using
a dedicated form parameter.

**Order of precedence is not modified.**
