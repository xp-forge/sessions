Sessions for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 3.0.0 / ????-??-??

* Merged PR #11: Fold transport functionality into sessions - @thekid

## 2.1.2 / 2021-10-21

* Made compatible with XP 11 - @thekid

## 2.1.1 / 2021-09-26

* Made compatible with XP web 3.0, see xp-forge/web#83 - @thekid

## 2.1.0 / 2021-01-03

* Made `web.session.ISession` interface implement `lang.Closeable`
  (@thekid)

## 2.0.0 / 2020-04-10

* Implemented xp-framework/rfc#334: Drop PHP 5.6:
  . **Heads up:** Minimum required PHP version now is PHP 7.0.0
  . Rewrote code base, grouping use statements
  . Rewrote `isset(X) ? X : default` to `X ?? default`
  (@thekid)

## 1.0.2 / 2019-12-01

* Made compatible with XP 10 - @thekid

## 1.0.1 / 2019-09-13

* Fixed cookie transport to transmit path and domain when deleting the
  cookie. See PR #9 for discussion.
  (@thekid)

## 1.0.0 / 2019-08-23

* **Heads up:** The `Sessions::in($path)` and `Sessions::insecure()`
  methods modifying cookie attributes have been removed and are now in
  the `web.session.Cookies` class, alongside others.
  (@thekid)
* Merged PR #8: Extract session transport to its own class - @thekid

## 0.6.0 / 2019-06-13

* Merged PR #6: Secure session cookies
  - Changed session cookie to be transmitted via HTTPS only by default.
  - Added web.session.Sessions::insecure(bool $whether) to restore old behaviour.
  (@mikey179)

## 0.5.0 / 2018-08-22

* Changed `ISession::remove()` to return whether the value being deleted
  previously existed or not, and not *void*.
  (@thekid)

## 0.4.0 / 2018-08-07

* Merged PR #5: Add web.session.ISession::keys() and implementations
  (@thekid)

## 0.3.0 / 2018-06-05

* Merged PR #3: Refactor session API: Standalone use vs. with request
  and response. **Heads up:** This contains a breaking API change!
  (@thekid)

## 0.2.0 / 2018-05-29

* Added `Sessions::in($path)` method to be able to influence the session 
  cookies' paths, which defaults to "/"
  (@thekid)

## 0.1.0 / 2018-05-18

* Merged PR #2: Tie sessions to request / response - @thekid
* Merged PR #1: Implement a filesystem-based session - @thekid
* Initial implementation - (@thekid)
