<?php
namespace TYPO3\AccountManagement\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.AccountManagement"*
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Policy\Role;

/**
 * Role controller for the TYPO3.AccountManagement package
 *
 * @Flow\Scope("singleton")
 */
class RoleController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 */
	protected $policyService;

	/**
	 * Shows a list of registers
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('roles', $this->policyService->getRoles());
	}

	/**
	 * Show Role details
	 *
	 * @param Role $role
	 * @return void
	 */
	public function showAction(Role $role) {
		$this->view->assign('role', $role);
	}
}

?>