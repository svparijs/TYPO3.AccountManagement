<?php
namespace TYPO3\UserManagement\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.UserManagement".  *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Account controller for the TYPO3.UserManagement package
 *
 * @Flow\Scope("singleton")
 */
class AccountManagementService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountFactory
	 */
	protected $accountFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 */
	protected $policyService;

	/**
	 * Create a new user
	 *
	 * This command creates a new user which has access to the backend user interface.
	 * It is recommended to user the email address as a username.
	 *
	 * @param string $username The username of the user to be created.
	 * @param string $password Password of the user to be created
	 * @param string $firstName First name of the user to be created
	 * @param string $lastName Last name of the user to be created
	 * @param string $roles A comma separated list of roles to assign
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function createUser($username, $password, $firstName, $lastName, $roles, $authenticationProvider = 'DefaultProvider') {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, $authenticationProvider);
		if ($account instanceof \TYPO3\Flow\Security\Account) {
				// Return exception
			return sprintf('User "%s" already exists.', array($username));
		}

		$user = new \TYPO3\Party\Domain\Model\Person;
		$name = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName, '', $username);
		$user->setName($name);

		$this->partyRepository->add($user);

		$account = $this->accountFactory->createAccountWithPassword($username, $password, explode(',', $roles), $authenticationProvider);
		$account->setParty($user);
		$this->accountRepository->add($account);

		return TRUE;
	}

	/**
	 * Set a new password for the given user
	 *
	 * This allows for setting a new password for an existing user account.
	 *
	 * @param string $username Username of the account to modify
	 * @param string $password The new password
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function setPasswordCommand($username, $password, $authenticationProvider = 'DefaultProvider') {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, $authenticationProvider);
		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			return FALSE;
		}
		$account->setCredentialsSource($this->hashService->hashPassword($password, 'default'));
		$this->accountRepository->update($account);

		return TRUE;
	}

	/**
	 * Add a role to a user
	 *
	 * This command allows for adding a specific role to an existing user.
	 * Currently supported roles: "Editor", "Administrator"
	 *
	 * @param string $username The username
	 * @param string $role Role ot be added to the user
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function addRoleCommand($username, $role, $authenticationProvider = 'DefaultProvider') {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, $authenticationProvider);
		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('User "%s" does not exists.', array($username));
			$this->quit(1);
		}

		$role = new \TYPO3\Flow\Security\Policy\Role($role);

		if ($account->hasRole($role)) {
			$this->outputLine('User "%s" already has the role "%s" assigned.', array($username, $role));
			$this->quit(1);
		}

		$account->addRole($role);
		$this->accountRepository->update($account);
		$this->outputLine('Added role "%s" to user "%s".', array($role, $username));
	}

	/**
	 * Remove a role from a user
	 *
	 * @param string $username Email address of the user
	 * @param string $role Role ot be removed from the user
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function removeRoleCommand($username, $role, $authenticationProvider = 'DefaultProvider') {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, $authenticationProvider);
		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('User "%s" does not exists.', array($username));
			$this->quit(1);
		}

		$role = new \TYPO3\Flow\Security\Policy\Role($role);

		if (!$account->hasRole($role)) {
			$this->outputLine('User "%s" does not have the role "%s" assigned.', array($username, $role));
			$this->quit(1);
		}

		$account->removeRole($role);
		$this->accountRepository->update($account);
		$this->outputLine('Removed role "%s" from user "%s".', array($role, $username));
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
	 * @see typo3.usermanagement:account:show
	 */
	public function listCommand($identifierFilter = NULL, $limit = 100) {
		$query = $this->accountRepository->createQuery();
		if ($identifierFilter !== NULL) {
			$query->matching($query->like('accountIdentifier', $identifierFilter, FALSE));
		}
		$result = $query->execute();

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
	 * @see typo3.usermanagement:account:list
	 */
	public function showCommand($identifier, $authenticationProvider = NULL) {
		$account = $this->getAccountByIdentifierOrAuthenticationProviderName($identifier, $authenticationProvider);

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
	 * Tries to find an account by its identifier only
	 * If this is ambiguous due to multiple authentication provider names, or if no Account could be found at all, the CLI execution is halted.
	 *
	 * @param string $identifier
	 * @param string $authenticationProvider
	 * @return \TYPO3\Flow\Security\Account
	 */
	protected function getAccountByIdentifierOrAuthenticationProviderName($identifier, $authenticationProvider = NULL) {
		if ($authenticationProvider !== NULL) {
			$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($identifier, $authenticationProvider);
		} else {
			$accounts = $this->accountRepository->findByAccountIdentifier($identifier);
			if ($accounts->count() > 1) {
				$this->outputFormatted('The given account identifier is ambiguous across multiple authentication providers. Please call the command again with the intended authentication provider name.');
				$this->quit(1);
			}
			$account = $accounts->getFirst();
		}

		if ($account === NULL) {
			$this->outputLine('No Account could be found.');
			$this->quit(1);
		}

		return $account;
	}

}

?>