Sessions for the XP Framework
========================================================================

[![Build status on GitHub](https://github.com/xp-forge/sessions/workflows/Tests/badge.svg)](https://github.com/xp-forge/sessions/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
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
if ($session= $sessions->open($token)) { … }

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

Implementations
---------------
This library includes the following implementations:

* `web.session.InFileSystem` - using the local filesystem with serialized data
* `web.session.ForTesting` - in-memory sessions, for testing purposes

Other implementations provide solutions for clustering:

* https://github.com/xp-forge/redis-sessions
* https://github.com/xp-forge/mongo-sessions
* https://github.com/xp-forge/cookie-sessions

Secure
------

The [Secure flag](https://www.owasp.org/index.php/SecureFlag) is set for all session cookies. If you develop on localhost using *http* only, you will need to tell the sessions instance as follows:

```php
// This will omit the "Secure" flag from session cookies in dev environment
$sessions= new InFileSystem('/tmp');
if ('dev' === $this->environment->profile()) {
  $sessions->cookies()->insecure(true);
}
```