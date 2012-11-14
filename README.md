Security.Manager
================

A TYPO3 Flow package that manages accounts and login authentication.

This Security Manager tool is a lightweight single purpose authentication wrapper around a given package.
The package has the same features that are provided in the security framework of TYPO3.Flow and require only a little
configuration.

Usage:
- Inspiration
- Security layer for any application

Authentication setup
--------------------

The initial view will show a login box.

When authenticated but not configured to redirect to a package, it will jump to the signedInAction by default.

Account Registration
--------------------

When the registration features is enable in the Settings.yaml, the link to the registration form is enabled.
(The actions are redirected through AOP, if not enabled).

	Security:
		Manager:
			register: TRUE

#Create Account

There are

	./flow help user:create

#List Account

#Edit Account

#Delete Account


Account ViewHelper
------------------

Add the viewhelper to fluid and call the viewhelper function.

	{namespace secure=Security\Manager\ViewHelpers}

	<secure:account propertyPath="party.name" />

[WORK IN PROGRESS]
==================

- Settings
- Translations
	- NL
- AOP Redirection
- Register Account (Unauthenticated)

- Create Account (Authenticated)
	- Redirect to?
- Update Account
	- Redirect to?
- Delete Account
	- Redirect to?
- List Accounts
- Routes

[FUTURE STUFF]

- Workflow with email verification
- Admin approval
- Give specific package roles when registering