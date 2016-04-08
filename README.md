# SilverStripe Member Email Verification Module

This module modifies the registration/login process so that Members are required to validate their email address before they can log in.

Features:
 - Works out of the box without any configuration
 - Supports i18n
 - Works with custom registration forms and other 3rd party modules (e.g. [silverstripe/forum](https://github.com/silverstripe/silverstripe-forum))
 - All templates and strings are easy to override

## About
This module adds the following properties to the `Member` `DataObject`:
 - `Verified`
 - `VerificationString`
 - `VerificationEmailSent`

On creation of a new Member, the Member is assigned a unique `VerificationString` and sent an email containing a validation link. The user will not be allowed to log in until they've visited the validation link sent in the verification email. After visiting the validation link, `Validated` is set to true for the `Member` record, and the user is allowed to log in to the website.

If the user has lost or deleted their verification email, they can have it re-sent using a form action that is added to the login form.

## Requirements

 - SilverStripe 3.1 or higher

## Installation

> composer require "jordanmkoncz/silverstripe-memberemailverification"

## Customisation

All text strings can be overridden. To override the English text strings, create or modify your `mysite/lang/en.yml` file and override the values in this module's `lang/en.yml` file. To override the strings for additional languages, add the other languages in your `mysite/lang` folder.

The template for the verification email can also be overridden. Just create a file in your theme's `templates/email` folder (note the lowercase "e") called `VerificationEmail.ss`. Within this template you can access the `Member` object, `SiteConfig` object, and of course the `ValidationLink` that the user must visit to verify their email.

The templates for all controller actions in the Security extension can also be overridden if needed. Each controller action has its own template name that it will use if it exists, and falls back to using the `Security` template. For example, to override the template for the `Security` `verify_email` action, just create a file in your theme's `templates/Layout` folder called `Security_verify_email.ss`.

## Example Project

You can view an example project that uses this module [here](https://github.com/jordanmkoncz/silverstripe-memberemailverification-example).

## Credits

This module was inspired by the [exadium/silverstripe-module-email-verified-member](https://github.com/marijnkampf/SilverStripe-Module-EmailVerifiedMember) module. It was created to provide the same member email verification functionality but without the unnecessary member moderation functionality, and with cleaner and more well-documented code that is easier to understand and easier to customise.