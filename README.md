Sessions for the XP Framework
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/sessions.png)](http://travis-ci.org/xp-forge/sessions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.6+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_6plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/sessions/version.png)](https://packagist.org/packages/xp-forge/sessions)

Example
-------

```php
use web\session\{InFileSystem, ForTesting};

// Instantiate session factory
$sessions= new InFileSystem('/tmp');
$sessions= (new ForTesting())->lasting(3600)->named('psessionid');

// Create a new session
$session= $sessions->create();

// Open an existing session...
if ($session= $sessions->open($sessionId)) { … }

// ...or locate session attached to a request
if ($session= $sessions->locate($request)) { … }

// Basic I/O operations
$session->register('key', 'value');
$value= $session->value('key');
$keys= $session->keys();
$session->remove('key');

// Destroy
$session->destroy();

// Close session...
$session->close();

// ...or close and then transmit session to response.
$session->transmit($response);
```

Ensure you always either call `close()` or `transmit()` to have the session data synchronized.

Secure
------

As of 0.6.0, the [Secure flag](https://www.owasp.org/index.php/SecureFlag) is set for all session cookies. If you develop on localhost using *http* only, you will need to tell the sessions instance as follows:

```php
// This will omit the "Secure" flag from session cookies
$sessions= (new InFileSystem('/tmp'))->insecure(true);
```