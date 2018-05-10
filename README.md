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
$sessions= (new ForTesting())->lasting(3600);

// Create a new session
$session= $sessions->create();

// Open an existing session
$session= $sessions->open($request);

// ...or, if you'd like to do this conditionally
if ($session= $sessions->locate($request)) { … }

// Basic I/O operations
$session->register('key', 'value');
$value= $session->value('key');
$session->remove('key');

// Destroy
$session->destroy();

// Finally, transmit session to response. Ensure you always call this!
$session->transmit($response);
```