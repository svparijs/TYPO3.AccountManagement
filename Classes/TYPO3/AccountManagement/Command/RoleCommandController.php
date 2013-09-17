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

/**
 * Command controller for tasks related to role handling
 *
 * @Flow\Scope("singleton")
 */
class RoleCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 */
	protected $policyService;

	/**
	 * Lists the Roles of this installation
	 *
	 * @return void
	 * @see typo3.usermanagement:account:show
	 */
	public function listCommand() {
		/** @var $role \TYPO3\Flow\Security\Policy\Role */
		foreach ($this->policyService->getRoles() as $roleIdentifier => $role) {
			$this->outputLine('%s %s', array(str_pad(sprintf('(%s)', $role->getSourceHint()), 8, ' ', STR_PAD_RIGHT), $roleIdentifier));
		}
	}

	/**
	 * Create a role with the given identifier
	 *
	 * @param string $identifier The Role identifier
	 * @return void
	 */
	public function createCommand($identifier) {
		try {
			$this->policyService->createRole($identifier);
		} catch (RoleExistsException $exception) {
			$this->outputLine('Error: %s', array($exception->getMessage()));
			$this->quit(1);
		} catch (\InvalidArgumentException $exception) {
			$this->outputLine('Error: %s', array($exception->getMessage()));
			$this->quit(1);
		}
		$this->outputLine('Role has successfully been added.');
	}

	/**
	 * Shows information about a Role, like a hint about the source or its parent roles.
	 *
	 * @param string $identifier The Role identifier
	 * @return void
	 */
	public function showCommand($identifier) {
		try {
			$role = $this->policyService->getRole($identifier);
		} catch (NoSuchRoleException $exception) {
			$this->outputLine('The role does not exist.');
			$this->quit(1);
		}
		$this->outputLine('Identifier:  "%s"', array($role->getIdentifier()));
		$this->outputLine('Source hint: "%s"', array($role->getSourceHint()));
		if (count($role->getParentRoles()) > 0) {
			$this->outputLine('Parent roles:');
			foreach ($role->getParentRoles() as $roleIdentifier => $role) {
				$this->outputFormatted('%s (%s)', array($roleIdentifier, $role->getSourceHint()), 4);
			}
		} else {
			$this->outputLine('No parent roles.');
		}
	}

	/**
	 * Tells whether a Role for the given identifier exists
	 *
	 * @param string $identifier The Role identifier
	 * @return void
	 */
	public function existsCommand($identifier) {
		try {
			$role = $this->policyService->getRole($identifier);
			$this->outputLine('The role exists, is a %s role.', array($role->getSourceHint()));
		} catch (NoSuchRoleException $exception) {
			$this->outputLine('The role does not exist.');
		}
	}

}

?>