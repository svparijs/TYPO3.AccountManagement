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

/**
 * Account controller for the TYPO3.AccountManagement package
 *
 * @Flow\Scope("singleton")
 */
class AccountController extends \TYPO3\Flow\Mvc\Controller\ActionController {

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
	 * @return void
	 */
	public function newAction() {
		$this->view->assign('roles', $this->policyService->getRoles());
	}

	/**
	 * Adds the given new account object to the account repository
	 *
	 * @param string $identifier
	 * @Flow\Validate(argumentName="identifier", type="NotEmpty")
	 * @Flow\Validate(argumentName="identifier", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @Flow\Validate(argumentName="identifier", type="\TYPO3\UserManagement\Validation\Validator\AccountExistsValidator")
	 * @param array $password
	 * @Flow\Validate(argumentName="password", type="\TYPO3\UserManagement\Validation\Validator\PasswordValidator")
	 * @param string $email
	 * @Flow\Validate(argumentName="email", type="NotEmpty")
	 * @Flow\Validate(argumentName="email", type="\TYPO3\Flow\Validation\Validator\EmailAddressValidator")
	 * @param string $firstName
	 * @Flow\Validate(argumentName="firstName", type="NotEmpty")
	 * @Flow\Validate(argumentName="firstName", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @param string $lastName
	 * @Flow\Validate(argumentName="lastName", type="NotEmpty")
	 * @Flow\Validate(argumentName="lastName", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @param string $roles
	 * @return void
	 */
	public function createAction($identifier, array $password, $email, $firstName, $lastName, $roles) {

		$name = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName, '', $identifier);
		$user->setName($name);
		$electrinocAddress = new \TYPO3\Party\Domain\Model\ElectronicAddress();
		$electrinocAddress->setIdentifier($email);
		$electrinocAddress->setType('Email');
		$user->setPrimaryElectronicAddress($electrinocAddress);
		$this->partyRepository->add($user);

		$account = $this->accountFactory->createAccountWithPassword($identifier, array_shift($password), array($roles), 'DefaultProvider');
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
	 * @return void
	 */
	public function resetPasswordAction() {
		$tokens = $this->securityContext->getAuthenticationTokens();

		foreach ($tokens as $token) {
			if ($token->isAuthenticated()) {
				$account = $this->accountManagementService->getAccount((string)\TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($token->getAccount(), 'accountidentifier'));
			}
		}

		if ($account instanceof \TYPO3\Flow\Security\Account) {
			$this->view->assign('account', $account);
		}
	}

	/**
	 * Updates the given account object
	 *
	 * @param \TYPO3\Flow\Security\Account $account
	 * @return void
	 */
	public function updateAction(\TYPO3\Flow\Security\Account $account) {

		$this->accountRepository->update($account);
		$this->partyRepository->update($account->getParty());

		$this->addFlashMessage('The user profile has been updated.');
		$this->redirect('index');
	}

	/**
	 * @param \TYPO3\Flow\Security\Account $account
	 * @return void
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
	public function backAction() {
		if (isset($this->settings['Redirect']['backToLink'])) {
			$redirect = $this->settings['Redirect']['backToLink'];
			$this->redirect($redirect['actionName'], $redirect['controllerName'], $redirect['packageKey']);
		}
		$this->redirect('index');
	}
}

?>