Security.Manager
================

A TYPO3 Flow package that manages accounts and login authentication.

This Security Manager tool is a lightweight single purpose authentication wrapper around a given package.
The package has the same features that are provided in the security framework of TYPO3.Flow and require only a little
configuration.

Usage:
- Security layer for any application
- Inspiration

Authentication setup
--------------------

The initial view will show a login box.

When authenticated but not configured to redirect to a package, it will jump to the signedInAction by default.
The signedIn view will show you with what "account.identifier" you have been signed into Flow.

Through Settings.yaml you will be able to configure options like redirections to a package, open registration for anonymous users
and so on. The package and its features will grow overtime if there is enough usage and are generic use cases to be applied. Feel
free to contribute, fork or leave a note.

Account Registration
--------------------

When the registration features is enable in the Settings.yaml, the link to the registration form is enabled.

(The actions are redirected through AOP, if not enabled).

	Security:
		Manager:
			register: TRUE

####Create Account

There are 2 ways to create a user with this package, through the CLI and through the frontend.

Usage through CLI:

	./flow help user:create
	./flow user:create --username testuser1 --password newPassword --first-name John --last-name Doe --roles Administrator

Usage through the frontend:

	http://localhost/login/index

####List Account

####Edit Account

Account ViewHelper
------------------

Add the viewhelper to fluid and call the viewhelper function.

	{namespace secure=Security\Manager\ViewHelpers}

	<secure:account propertyPath="party.name" />

Security walk-through
---------------------

The way the TYPO3.Flow framework enables us to secure packages makes it easy to incorporate the Security.Manager package with its authentication features.
For an example heres how the Security.Manager packages secures itself agains unauthorized access.

	resources:
	  methods:
	    Security_ManagerSignedInMethods: 'method(Security\Manager\Controller\LoginController->(signedIn)Action())'
	    Security_ManagerAccountMethods: 'method(Security\Manager\Controller\RegisterController->(index|new|edit|update|delete)Action())'
	roles:
	  Editor: []
	acls:
	  Editor:
	    methods:
	      Security_ManagerSignedInMethods: GRANT
	      Security_ManagerAccountMethods: GRANT

When the action is unauthorized the TYPO3.Flow framework will redirect the package to a location set with the Settings.yaml configuration.

	TYPO3:
	  Flow:
	    security:
	      authentication:
	        providers:
	          DefaultProvider:
	            entryPoint: 'WebRedirect'
	            entryPointOptions:
	              uri: 'login/index'

Securing your package
---------------------

#####Routing

To be able to address the login feature you will need to add these routes in the general Routes.yaml

	-
	  name: 'Security'
	  uriPattern: '<SecuritySubroutes>'
	  subRoutes:
	    SecuritySubroutes:
	      package: Security.Manager

######Securing your package

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

[WORK IN PROGRESS]
==================

- Settings
- Routes
- Policy
	- Restrict added to Register Expect RegisterAccount
- Translations
	- NL
- AOP
	- Redirection user: Anonymous if Registration is not allowed
- RegisterAccount (Unauthenticated)
- New (Authenticated)
- Create Account (Authenticated)
	- Redirect to settings option - default behavior
- Update Account
	- Redirect to settings option - default behavior
- Delete Account
	- Redirect to settings option - default behavior
- List Accounts
- Functional tests
	- Check security layer

[FUTURE STUFF]

- Workflow with email verification
- Admin approval
- Give specific package roles when registering