<?php
namespace TYPO3\UserManagement\Command;

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

/**
 * Command controller for tasks related to account handling
 *
 * @Flow\Scope("singleton")
 */
class AccountCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

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
			$query->matching($query->like('accountIdentifier',$identifierFilter, FALSE));
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
	 * @param string $authenticationProviderName The authentication provider name
	 * @return void
	 * @see typo3.usermanagement:account:list
	 */
	public function showCommand($identifier, $authenticationProviderName = 'DefaultProvider') {
		/** @var $account \TYPO3\Flow\Security\Account */
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($identifier, $authenticationProviderName);
		if ($account === NULL) {
			$this->outputLine('No Account could be found for the given identifier and authentication provider name.');
			return;
		}

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
			/** @var $role \TYPO3\Flow\Security\Policy\Role */
			foreach ($account->getRoles() as $roleIdentifier => $role) {
				$parentRoles = $role->getParentRoles();
				$parentRolesString = $parentRoles ? implode($parentRoles, ', ') : 'none';
				$this->outputFormatted('%s (parents: %s)', array($roleIdentifier, $parentRolesString), 4);
			}
		}
	}

}

?>