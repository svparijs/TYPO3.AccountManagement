<?php
namespace TYPO3\UserManagement\Controller;

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
class AccountController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

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
			$propertyMappingConfigurationForAccountParty->setTypeConverterOption(
				'TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter',
				\TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_TARGET_TYPE,
				'\TYPO3\UserManagement\Domain\Model\User'
			);

			foreach (array($propertyMappingConfigurationForAccountParty, $propertyMappingConfigurationForAccountPartyName) as $propertyMappingConfiguration) {
				$propertyMappingConfiguration->setTypeConverterOption(
					'TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter',
					\TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
					TRUE
				);
				$propertyMappingConfiguration->setTypeConverterOption(
					'TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter',
					\TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED,
					TRUE
				);
			}
		}
	}

	/**
	 * Shows a list of registers
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('accounts', $this->accountRepository->findAll());
	}

	/**
	 * Shows a account object
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 * @return void
	 */
	public function showAction(\TYPO3\Flow\Security\Account $account) {
		$this->view->assign('account', $account);
	}

	/**
	 * Shows a form for creating a new account object
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 * @return void
	 */
	public function registerAction(\TYPO3\Flow\Security\Account $account = NULL) {
		$this->view->assign('account', $account);
		$this->view->assign('availableRoles', $this->policyService->getRoles());
	}

	/**
	 * Show the registration successfull page and redirects to the login page
	 * @return void
	 */
	public function onRegistrationSucces(){
		$this->redirect('index', 'Login', NULL, array(), 400);
	}

	/**
	 * Shows a form for creating a new account object
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 * @return void
	 */
	public function newAction(\TYPO3\Flow\Security\Account $account = NULL) {
		$this->view->assign('account', $account);
	}

	/**
	 * Adds the given new account object to the account repository
	 *
	 * @param string $identifier
	 * @Flow\Validate(argumentName="identifier", type="NotEmpty")
	 * @Flow\Validate(argumentName="identifier", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @Flow\Validate(argumentName="identifier", type="\TYPO3\UserManagement\Validation\Validator\AccountExistsValidator")
	 * @param array $password
	 * @Flow\Validate(argumentName="password", type="\TYPO3\UserManagement\Validation\Validator\PasswordValidator", options={ "allowEmpty"=0, "minimum"=1, "maximum"=255 })
	 * @param string $email
	 * @Flow\Validate(argumentName="email", type="NotEmpty")
	 * @Flow\Validate(argumentName="email", type="\TYPO3\Flow\Validation\Validator\EmailAddressValidator")
	 * @param string $firstName
	 * @Flow\Validate(argumentName="firstName", type="NotEmpty")
	 * @Flow\Validate(argumentName="firstName", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @param string $lastName
	 * @Flow\Validate(argumentName="lastName", type="NotEmpty")
	 * @Flow\Validate(argumentName="lastName", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @return void
	 * @todo Security
	 */
	public function createAction($identifier, array $password, $email, $firstName, $lastName, $role) {
		$user = new \Security\Manager\Domain\Model\User();
		$name = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName, '', $identifier);
		$user->setName($name);
		$this->partyRepository->add($user);

		$account = $this->accountFactory->createAccountWithPassword($identifier, array_shift($password), array('Administrator'), 'DefaultProvider');
		$account->setParty($user);
		$this->accountRepository->add($account);

		$this->addFlashMessage('Created a new account.');
		$this->redirect('index');
	}

	/**
	 * Shows a form for editing an existing register object
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 * @return void
	 */
	public function editAction(\TYPO3\Flow\Security\Account $account) {
		$this->view->assign('account', $account);
	}

	/**
	 * Updates the given account object
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 * @param array $password
	 * @Flow\Validate(argumentName="password", type="\TYPO3\UserManagement\Validation\Validator\PasswordValidator", options={ "allowEmpty"=1, "minimum"=1, "maximum"=255 })
	 * @return void
	 * @todo Handle validation errors for account (accountIdentifier) & check if there's another account with the same accountIdentifier when changing it
	 * @todo Security
	 */
	public function updateAction(\TYPO3\Flow\Security\Account $account, array $password = array()) {
		$password = array_shift($password);
		if (strlen(trim(strval($password))) > 0) {
			$account->setCredentialsSource($this->hashService->hashPassword($password, 'default'));
		}

		$this->accountRepository->update($account);
		$this->partyRepository->update($account->getParty());

		$this->addFlashMessage('The user profile has been updated.');
		$this->redirect('index');
	}

	/**
	 * @param \TYPO3\Flow\Security\Account $account
	 * @return void
	 * @todo Security
	 */
	public function deleteAction(\TYPO3\Flow\Security\Account $account) {
		if ($this->securityContext->getAccount() === $account) {
			$this->addFlashMessage('You can not remove current logged in user');
			$this->redirect('index');
		}
		$this->accountRepository->remove($account);
		$this->addFlashMessage('The user has been deleted.');
		$this->redirect('index');
	}

	/**
	 * Redirects the action toward the configured back location
	 *
	 * @return void
	 */
	public function backAction(){
		if(isset($this->settings['Redirection']['indexBack'])) {
			$redirection = $this->settings['Redirection']['indexBack'];
			$this->redirect($redirection['Action'], $redirection['Controller'], $redirection['Package']);
		}
		$this->redirect('index', 'Login');
	}

}

?>