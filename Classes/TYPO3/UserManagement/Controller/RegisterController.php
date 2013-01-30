<?php
namespace TYPO3\UserManagement\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.UserManagement".      *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

use TYPO3\Flow\Mvc\Controller\ActionController;

/**
 * Register controller for the TYPO3.UserManagement package
 *
 * @Flow\Scope("singleton")
 */
class RegisterController extends ActionController {

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
	 * @return void
	 */
	protected function initializeAction() {
		parent::initializeAction();
		if ($this->arguments->hasArgument('account')) {
			$propertyMappingConfigurationForAccount = $this->arguments->getArgument('account')->getPropertyMappingConfiguration();
			$propertyMappingConfigurationForAccountParty = $propertyMappingConfigurationForAccount->forProperty('party');
			$propertyMappingConfigurationForAccountPartyName = $propertyMappingConfigurationForAccount->forProperty('party.name');
			$propertyMappingConfigurationForAccountParty->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_TARGET_TYPE, '\TYPO3\UserManagement\Domain\Model\User');
			foreach (array($propertyMappingConfigurationForAccountParty, $propertyMappingConfigurationForAccountPartyName) as $propertyMappingConfiguration) {
				$propertyMappingConfiguration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
				$propertyMappingConfiguration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
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
	 * @Flow\Validate(argumentName="identifier", type="\TYPO3\UserManagement\Validation\Validator\AccountExistsValidator", options={ "authenticationProviderName"="Typo3BackendProvider" })
	 * @param array $password
	 * @Flow\Validate(argumentName="password", type="\TYPO3\UserManagement\Validation\Validator\PasswordValidator", options={ "allowEmpty"=0, "minimum"=1, "maximum"=255 })
	 * @param string $firstName
	 * @Flow\Validate(argumentName="firstName", type="NotEmpty")
	 * @Flow\Validate(argumentName="firstName", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @param string $lastName
	 * @Flow\Validate(argumentName="lastName", type="NotEmpty")
	 * @Flow\Validate(argumentName="lastName", type="StringLength", options={ "minimum"=1, "maximum"=255 })
	 * @return void
	 * @todo TYPO3
	 */
	public function createAction($identifier, array $password, $firstName, $lastName) {
		$user = new \TYPO3\UserManagement\Domain\Model\User();
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
	 * Edit account profile
	 *
	 * @return void
	 */
	public function editProfileAction(){
		$this->view->assign('account', $this->securityContext->getAccount());
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
	 * @param \TYPO3\Flow\TYPO3\Account $account
	 * @param array $password
	 * @Flow\Validate(argumentName="password", type="\TYPO3\UserManagement\Validation\Validator\PasswordValidator", options={ "allowEmpty"=1, "minimum"=1, "maximum"=255 })
	 * @return void
	 * @todo Handle validation errors for account (accountIdentifier) & check if there's another account with the same accountIdentifier when changing it
	 * @todo TYPO3
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
	 * @param \TYPO3\Flow\TYPO3\Account $account
	 * @return void
	 * @todo TYPO3
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

		$this->redirect('index', 'Login');
	}

}

?>