<?php
namespace TYPO3\AccountManagement\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Exception\NoSuchRoleException;
use TYPO3\Flow\Security\Exception\RoleExistsException;
use TYPO3\Flow\Security\Policy\Role;

/**
 * Command controller for tasks related to account handling
 *
 * @Flow\Scope("singleton")
 */
class AccountCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\AccountManagement\Service\AccountManagementService
	 */
	protected $accountManagementService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Create a new account
	 *
	 * This command creates a new Account which has access to the backend Account interface.
	 * It is recommended to user the email address as a username.
	 *
	 * @param string $username The username of the user to be created.
	 * @param string $password Password of the user to be created
	 * @param string $firstName First name of the user to be created
	 * @param string $lastName Last name of the user to be created
	 * @param string $roles A comma separated list of roles to assign
	 * @param string $middleName Middle name of the user to be created (optional)
	 * @param string $authenticationProvider The name of the authentication provider to use (optional)
	 * @return void
	 */
	public function createCommand($username, $password, $firstName, $lastName, $roles, $middleName='', $authenticationProvider = 'DefaultProvider') {

		$result = $this->accountManagementService->createUser($username, $password, $firstName, $middleName, $lastName, $roles, $authenticationProvider);

		if ($result instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('Created account "%s".', array($username));
		} else {
			$this->outputLine('User "%s" already exists.', array($username));
			$this->quit(1);
		}
	}

	/**
	 * Remove a account
	 *
	 * This command removes a user which has access to the backend user interface.
	 *
	 * @param string $identifier The username of the account to be removed.
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function removeCommand($identifier, $authenticationProvider = 'DefaultProvider') {

		$result = $this->accountManagementService->removeUser($identifier, $authenticationProvider);

		if (!$result) {
			$this->outputLine('User "%s" does not exist.', array($identifier));
			$this->quit(1);
		} else {
			$this->outputLine('User "%s" is removed.', array($identifier));
		}
	}

	/**
	 * Set a new password for the given account
	 *
	 * This allows for setting a new password for an existing user account.
	 *
	 * @param string $username Username of the account to modify
	 * @param string $password The new password
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function setPasswordCommand($username, $password, $authenticationProvider = 'DefaultProvider') {

		$account = $this->accountManagementService->getAccount($username, $authenticationProvider);

		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('User "%s" does not exists.', array($username));
			$this->quit(1);
		}

		$this->accountManagementService->resetPassword($account, $password);

		$this->outputLine('The new password for user "%s" was set.', array($username));
	}

	/**
	 * Lists the Accounts of this installation
	 *
	 * The list can be filtered to match a particular pattern,
	 * and will be limited to a configurable amount of items shown.
	 *
	 * @param string $identifierFilter A filter string, matching the "LIKE" requirements for Repositories. Case-insensitive.
	 * @param integer $limit The maximum amount of accounts shown
	 * @return void
	 * @see typo3.accountmanagement:account:show
	 */
	public function listCommand($identifierFilter = NULL, $limit = 100) {

		$result = $this->accountManagementService->getAccountList($identifierFilter, $limit);

		$this->outputLine('Creation date            Expiration date          Auth. prov. name     Identifier');
		$this->outputLine('------------------------ ------------------------ -------------------- -------------');

		/** @var $account \TYPO3\Flow\Security\Account */

		$displayCount = 0;
		foreach ($result as $account) {
			$this->outputLine('%s %s %s %s', array(
				$account->getCreationDate() ? $account->getCreationDate()->format(\DateTime::ISO8601) : 'NULL' . str_repeat(' ', 20),
				$account->getExpirationDate() ? $account->getExpirationDate()->format(\DateTime::ISO8601) : 'NULL' . str_repeat(' ', 20),
				str_pad($account->getAuthenticationProviderName(), 20, ' ', STR_PAD_RIGHT),
				$account->getAccountIdentifier()
			));
			$displayCount++;
			if ($displayCount >= $limit) {
				break;
			}
		}

		$this->outputLine();
		$this->outputLine('Displayed %d of %d accounts', array($displayCount, $result->count()));
	}

	/**
	 * Shows particular data for a given Account
	 *
	 * @param string $identifier The account identifier to show information about
	 * @param string $authenticationProvider The name of the authentication provider. Can be left out if account identifier is unambiguous
	 * @return void
	 * @see typo3.accountmanagement:account:list
	 */
	public function showCommand($identifier, $authenticationProvider = NULL) {
		$account = $this->accountManagementService->getAccount($identifier, $authenticationProvider);

		$this->outputLine('Identifier:              %s', array($account->getAccountIdentifier()));
		$this->outputLine('Authentication Provider: %s', array($account->getAuthenticationProviderName()));
		$this->outputLine('Creation date:           %s', array($account->getCreationDate() ? $account->getCreationDate()->format(\DateTime::ISO8601) : 'NULL'));
		$this->outputLine('Expiration date:         %s', array($account->getExpirationDate() ? $account->getExpirationDate()->format(\DateTime::ISO8601) : 'NULL'));

		$this->outputLine();
		$this->outputLine('Party:');
		if ($account->getParty() === NULL) {
			$this->outputFormatted('not set', array(), 4);
		} else {
			$this->outputFormatted('Entity    : %s', array($this->reflectionService->getClassNameByObject($account->getParty())), 4);
			$this->outputFormatted('Identifier: %s', array($this->persistenceManager->getIdentifierByObject($account->getParty())), 4);
			if (method_exists($account->getParty(), '__toString')) {
				$this->outputFormatted('String:     %s', array((string)$account->getParty()), 4);
			}
		}

		$this->outputLine();
		$this->outputLine('Roles:');
		if (count($account->getRoles()) === 0) {
			$this->outputFormatted('none set', array(), 4);
		} else {
			/** @var $role Role */
			foreach ($account->getRoles() as $roleIdentifier => $role) {
				$parentRoles = $role->getParentRoles();
				$parentRolesString = $parentRoles ? implode($parentRoles, ', ') : 'none';
				$this->outputFormatted('%s (parents: %s)', array($roleIdentifier, $parentRolesString), 4);
			}
		}
	}

	/**
	 * @param string $identifier The account identifier to add the role to
	 * @param string $role The name of the role to add
	 * @param string $authenticationProvider The name of the authentication provider. Can be left out if account identifier is unambiguous
	 * @return void
	 * @see typo3.accountmanagement:account:show
	 * @see typo3.accountmanagement:account:removeRole
	 */
	public function addRoleCommand($identifier, $role, $authenticationProvider = NULL) {
		$account = $this->accountManagementService->getAccount($identifier, $authenticationProvider);

		try {
			$role = $this->accountManagementService->getRole($role);
		} catch (NoSuchRoleException $exception) {
			try {
				$role = $this->accountManagementService->createRole($role);
			} catch (RoleExistsException $exception) {
				$this->outputLine('Error: %s', array($exception->getMessage()));
				$this->quit(1);
			} catch (\InvalidArgumentException $exception) {
				$this->outputLine('Error: %s', array($exception->getMessage()));
				$this->quit(1);
			}
		}

		if ($account->hasRole($role)) {
			$this->outputLine('Error: Account already has the role assigned.');
			$this->quit(1);
		} else {
			$account->addRole($role);
			$this->accountManagementService->updateAccount($account);
			$this->outputLine('Role has been added to the Account.');
		}
	}

	/**
	 * @param string $identifier The account identifier to remove the role from
	 * @param string $role The name of the role to remove
	 * @param string $authenticationProvider The name of the authentication provider. Can be left out if account identifier is unambiguous
	 * @return void
	 * @see typo3.accountmanagement:account:show
	 * @see typo3.accountmanagement:account:addRole
	 */
	public function removeRoleCommand($identifier, $role, $authenticationProvider = NULL) {
		$account = $this->accountManagementService->getAccount($identifier, $authenticationProvider);

		try {
			$role = $this->accountManagementService->getRole($role);
		} catch (NoSuchRoleException $exception) {
			$this->outputLine('Error: The given role does not exist.');
			$this->quit(1);
		}

		if (!$account->hasRole($role)) {
			$this->outputLine('Error: Account does not have the role assigned.');
			$this->quit(1);
		} else {
			$account->removeRole($role);
			$this->accountManagementService->updateAccount($account);
			$this->outputLine('Role has been removed from the Account.');
		}
	}

}

?>