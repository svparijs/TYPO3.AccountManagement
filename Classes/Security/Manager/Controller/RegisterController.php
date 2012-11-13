<?php
namespace Security\Manager\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Security.Manager".      *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Register controller for the Security.Manager package
 *
 * @Flow\Scope("singleton")
 */
class RegisterController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('foos', array(
			'bar', 'baz'
		));
	}

		/**
     * edit the account properties
     * @param \TYPO3\Flow\Security\Account $account
		 * @return void
     */
    public function editAction(\TYPO3\Flow\Security\Account $account) {
		$this->view->assign('account', $account);
    }

	/**
	 * @param \TYPO3\Flow\Security\Account $account
	 * @param array $password
	 * ///@Flow\Validate(argumentName="password", type="\TYPO3\TYPO3\Validation\Validator\PasswordValidator", options={ "allowEmpty"=1, "minimum"=1, "maximum"=255 })
	 * @return void
	 */
    public function updateAction(\TYPO3\Flow\Security\Account $account, array $password = array()) {
		$password = array_shift($password);
		if (strlen(trim(strval($password))) > 0) {
			$account->setCredentialsSource($this->hashService->hashPassword($password, 'default'));
		}

		$this->accountRepository->update($account);
		$this->partyRepository->update($account->getParty());

		$this->addFlashMessage('The user profile has been updated.');
		$this->redirect('index', 'Overview\Overview');
    }

}

?>