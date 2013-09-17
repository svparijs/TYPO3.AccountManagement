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
use TYPO3\Flow\Security\Account;

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
			return FALSE;
		}

		$user = new \TYPO3\Party\Domain\Model\Person;
		$name = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName, '', $username);
		$user->setName($name);

		$this->partyRepository->add($user);

		$account = $this->accountFactory->createAccountWithPassword($username, $password, explode(',', $roles), $authenticationProvider);
		$account->setParty($user);
		$this->accountRepository->add($account);

		return $account;
	}

	/**
	 * Removes a user
	 *
	 * This command removes a user which has access to the backend user interface.
	 *
	 * @param string $identifier The username of the user to be removed.
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function removeUser($identifier, $authenticationProvider = 'DefaultProvider') {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($identifier, $authenticationProvider);
		if ($account instanceof \TYPO3\Flow\Security\Account) {
			$party = $account->getParty();

			$this->partyRepository->remove($party);
			$this->accountRepository->remove($account);

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Set a new password for the given account
	 *
	 * This allows for setting a new password for an existing user account.
	 *
	 * @param Account $account
	 * @param $password
	 * @param string $type
	 */
	public function setResetPassword(Account $account, $password, $type = 'default') {

		$account->setCredentialsSource($this->hashService->hashPassword($password, $type));
		$this->accountRepository->update($account);
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
	public function getAccountList($identifierFilter = NULL, $limit = 100) {
		$query = $this->accountRepository->createQuery();
		if ($identifierFilter !== NULL) {
			$query->matching($query->like('accountIdentifier', $identifierFilter, FALSE))
			->setLimit($limit);
		}

		return $query->execute();
	}

	/**
	 * Shows particular data for a given Account
	 *
	 * @param string $identifier The account identifier to show information about
	 * @param string $authenticationProvider The name of the authentication provider. Can be left out if account identifier is unambiguous
	 * @return void
	 * @see typo3.usermanagement:account:list
	 */
	public function getAccount($identifier, $authenticationProvider = NULL) {
		return $this->getAccountByIdentifierOrAuthenticationProviderName($identifier, $authenticationProvider);
	}

	/**
	 * Persist Updated account
	 *
	 * @param Account $account
	 */
	public function updateAccount(Account $account) {
		$this->accountRepository->update($account);
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
				return FALSE;
			}
			$account = $accounts->getFirst();
		}

		if ($account === NULL) {
			return FALSE;
		}

		return $account;
	}

	/**
	 * @param $role
	 * @return \TYPO3\Flow\Security\Policy\Role
	 */
	public function getRole($role) {
		return $this->policyService->getRole($role);
	}

	/**
	 * @param $role
	 * @return \TYPO3\Flow\Security\Policy\Role
	 */
	public function createRole($role) {
		return $this->policyService->createRole($role);
	}
}

?>