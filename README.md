TYPO3.UserManagement [![Build Status](https://travis-ci.org/svparijs/TYPO3.UserManagement.png?branch=master)](https://travis-ci.org/svparijs/TYPO3.UserManagement)
==================================================================================================================================================================

A TYPO3 Flow package that manages accounts and login authentication.

This User Management tool is a lightweight single purpose authentication wrapper around a given package.
In addition it handles all user CRUD actions.
The package is built on same features that are provided in the security framework of TYPO3.Flow and require only a little
configuration.

Usage:
- Security layer for any application
- User Management for any application
- Inspiration

Authentication setup
--------------------

The initial view will show a login box.

When authenticated but not configured, the package will redirect to the signedInAction by default.
The signedIn view will show you with what "account.identifier" you have been authenticated.

Through Settings.yaml you will be able to configure options like redirects to a package, open registration for anonymous users
and so on. The package and its features will grow overtime if there is enough usage and are generic use cases to be applied. Feel
free to contribute, fork or leave a note.

Quickstart
----------

This section will get you up and running.

#####Routing

To be able to address the login feature you will need to add these routes in the general Configuration/Routes.yaml

	-
	  name: 'Security'
	  uriPattern: '<SecuritySubroutes>'
	  subRoutes:
	    SecuritySubroutes:
	      package: TYPO3.UserManagement

####Create Account

There are 2 ways to create a user with this package, through the CLI and through the frontend.

#####Usage through CLI:

	./flow help user:create
	./flow user:create --username user1 --password newPassword --first-name John --last-name Doe --roles Administrator

#####Usage through the frontend:

[Not working yet]
The package will notice that there are no accounts yet, and will redirect you to a registration page where you can create your first account.

	http://localhost/login

[Not working yet]

####First authentication

Now you will be able to login. Use the credentials you filled in at this url (if your not already there).

	http://localhost/login

After signing in you will be redirected to the "Signed In Dummy" page, this page is to show that all the functionality works as intended.
Later on in this README you will see how to redirect to a page of your choice.

####List Account

####Edit Account

Account ViewHelper
------------------

Add the viewhelper to fluid and call the viewhelper function.

	{namespace secure=TYPO3\UserManagement\ViewHelpers}

	<secure:account propertyPath="party.name" />

Security walk-through
---------------------

The way the TYPO3.Flow framework enables us to secure packages makes it easy to incorporate the TYPO3.UserManagement package with its authentication features.
For an example heres how the TYPO3.UserManagement packages secures itself agains unauthorized access.

	resources:
	  methods:
	    TYPO3_UserManagementSignedInMethods: 'method(TYPO3\UserManagement\Controller\LoginController->(signedIn)Action())'
	    TYPO3_UserManagementAccountMethods: 'method(TYPO3\UserManagement\Controller\RegisterController->(index|new|edit|update|delete)Action())'
	roles:
	  Editor: []
	acls:
	  Editor:
	    methods:
	      TYPO3_UserManagementSignedInMethods: GRANT
	      TYPO3_UserManagementAccountMethods: GRANT

When the action is unauthorized the TYPO3.Flow framework will redirect the package to a location set with the Settings.yaml configuration.

	TYPO3:
	  Flow:
	    security:
	      authentication:
	        providers:
	          DefaultProvider:
	            entryPoint: 'WebRedirect'
	            entryPointOptions:
	              routeValues:
                    '@package': 'TYPO3.UserManagement'
                    '@controller': 'Login'
                    '@action': 'index'

Securing your package
---------------------

To secure your package from unauthorized access you will need to add some policies in your Configuration/Policy.yaml.

	resources:
	  methods:
	    MyCompany_PackageSomeAutherizedMethods: 'method(MyCompany\Package\Controller\DummyController->(index|show)Action())'
	roles:
	  Editor: []
	acls:
	  Editor:
	    methods:
	      MyCompany_PackageSomeAutherizedMethods: GRANT

See for reference: http://flow.typo3.org/documentation/guide/partiii/security.html