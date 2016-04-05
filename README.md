# SilverStripe Member Email Verification Module

This module modifies the registration/login process so that Members are required to validate their email address before they can log in.

This module adds the following properties to the `Member` `DataObject`:
 - `Verified`
 - `VerificationString`
 - `VerificationEmailSent`

On creation of a new Member, the Member is assigned a unique `VerificationString` and sent an email containing a validation link. The member will not be allowed to log in until they've visited the validation link sent in the verification email. After visiting the validation link, `Validated` is set to true for the `Member` record, and the member is allowed to log in to the website.

If the member has lost or deleted their verification email, they can have it re-sent using a form action that is added to the login form.

## Requirements

 - SilverStripe 3.1 or higher

## Installation

> composer require "jordanmkoncz/silverstripe-memberemailverification"

## Customisation
All text strings can be overridden. To override the English text strings, create or modify your `mysite/lang/en.yml` file and override the values in this module's `lang/en.yml` file. To override the strings for additional languages, add the other languages in your `mysite/lang` folder.

The templates for all controller actions in the Security extension can be overridden if needed. Each controller action has its own template name that it will use if it exists, and falls back to using the `Security` template.