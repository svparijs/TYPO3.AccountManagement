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
use TYPO3\Flow\Security\Account;

/**
 * Profile controller for the TYPO3.AccountManagement package
 *
 * @Flow\Scope("singleton")
 */
class ProfileController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\AccountManagement\Service\AccountManagementService
	 */
	protected $accountManagementService;

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
	 * @var \TYPO3\Flow\Security\Policy\PolicyService
	 * @Flow\Inject
	 */
	protected $policyService;

	/**
	 * @return void
	 */
	protected function initializeAction() {
		parent::initializeAction();
		if ($this->arguments->hasArgument('account')) {
			$propertyMappingConfigurationForAccount = $this->arguments->getArgument('account')->getPropertyMappingConfiguration();
			$propertyMappingConfigurationForAccountParty = $propertyMappingConfigurationForAccount->forProperty('party');
			$propertyMappingConfigurationForAccountPartyName = $propertyMappingConfigurationForAccount->forProperty('party.name');

			foreach (array($propertyMappingConfigurationForAccountParty, $propertyMappingConfigurationForAccountPartyName) as $propertyMappingConfiguration) {
				$propertyMappingConfiguration->setTypeConverterOption(
					'TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter',
					\TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED,
					TRUE
				);
			}
		}
	}

	/**
	 * Show account profile information
	 *
	 * @return void
	 */
	public function profileAction() {
		$this->view->assign('account', $this->accountManagementService->getProfile());
	}

	/**
	 * Show account settings
	 *
	 * @return void
	 */
	public function settingsAction() {
		$this->view->assign('account', $this->accountManagementService->getProfile());
	}

	/**
	 * Shows account permissions
	 *
	 * @return void
	 */
	public function permissionsAction() {
		$this->view->assign('account', $this->accountManagementService->getProfile());
		$this->view->assign('roles', $this->policyService->getRoles());
	}

	/**
	 * Show account password reset
	 *
	 * @return void
	 */
	public function resetPasswordAction() {
		$this->view->assign('account', $this->accountManagementService->getProfile());
	}

	/**
	 * @return void
	 */
	public function notificationCenterAction() {
		$this->view->assign('account', $this->accountManagementService->getProfile());
	}

	/**
	 * Updates the current signed account
	 *
	 * @param Account $account
	 * @return void
	 */
	public function updateAction(Account $account) {

		$this->accountRepository->update($account);
		$this->partyRepository->update($account->getParty());

		$this->addFlashMessage('The profile has been updated.');

		$referrer = $this->request->getReferringRequest();
		$this->redirect($referrer->getControllerActionName(), $referrer->getControllerName());
	}

	/**
	 * Updates the password for the signed user
	 *
	 * @param array $password
	 * @Flow\Validate(argumentName="password", type="\TYPO3\AccountManagement\Validation\Validator\PasswordValidator")
	 * @return void
	 */
	public function updatePasswordAction(array $password) {

		$account = $this->accountManagementService->getProfile();

		$this->accountManagementService->resetPassword($account, array_shift($password));

		$this->addFlashMessage('Password has been reset.');

		$referrer = $this->request->getReferringRequest();
		$this->redirect($referrer->getControllerActionName(), $referrer->getControllerName());
	}
}

?>