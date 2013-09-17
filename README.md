TYPO3.AccountManagement [![Build Status](https://travis-ci.org/svparijs/TYPO3.AccountManagement.png?branch=master)](https://travis-ci.org/svparijs/TYPO3.AccountManagement)
==================================================================================================================================================================

A TYPO3 Flow package that manages accounts.

This Account Management tool is a lightweight single purpose account management wrapper around a given package.
It handles all account & role CRUD actions.
The package is built on same features that are provided in the security framework of TYPO3.Flow and require only a little
configuration.

Usage:
- Account Management for any application (Flow, Neos)
- Account CRUD
- Role CRUD
- Inspiration

Quickstart
----------

This section will get you up and running.

#####Routing

To be able to address the Account Management you will need to add these routes in the general Configuration/Routes.yaml

	-
	  name: 'AccountManagement'
	  uriPattern: '<AccountManagementSubroutes>'
	  subRoutes:
	    AccountManagementSubroutes:
	      package: TYPO3.AccountManagement

####Create Account

There are 2 ways to create a user with this package, through the CLI and through the frontend.

#####Usage through CLI:

	./flow help account:create
	./flow account:create --username user1 --password newPassword --first-name John --last-name Doe --roles Administrator


### PACKAGE "TYPO3.ACCOUNTMANAGEMENT" CLI Commands

	PACKAGE "TYPO3.ACCOUNTMANAGEMENT":
	-------------------------------------------------------------------------------

	account:create                           Create a new account
	account:remove                           Remove a account
	account:setpassword                      Set a new password for the given account
	account:list                             Lists the Accounts of this installation
	account:show                             Shows particular data for a given Account
	account:addrole
	account:removerole

	role:list                                Lists the Roles of this installation
	role:create                              Create a role with the given identifier
	role:show                                Shows information about a Role, like a hint about the source or its parent roles.
	role:exists                              Tells whether a Role for the given identifier exists

Account ViewHelper
------------------

Add the viewhelper to fluid and call the viewhelper function.

	{namespace account=TYPO3\AccountManagement\ViewHelpers}

	<account:account propertyPath="party.name" />