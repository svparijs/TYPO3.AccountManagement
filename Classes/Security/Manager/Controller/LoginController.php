<?php
namespace Security\Manager\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Svparijs.BibleStudies". *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A controller which allows for logging into the backend
 *
 * @Flow\Scope("singleton")
 */
class LoginController extends \TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	/**
     * @var \TYPO3\Flow\Security\AccountRepository
     * @Flow\Inject
     */
    protected $accountRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 */
	protected $partyRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function loginAction($username = NULL) {
		$this->view->assign('username', $username);
		$this->view->assign('hostname', $this->request->getHttpRequest()->getBaseUri()->getHost());
		$this->view->assign('date', new \DateTime());
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

	/**
	 * Redirect action
	 *
	 * @return void
	 */
	public function redirectAction() {
		$this->redirect('index');
	}

	/**
	 * Is called if authentication failed.
	 *
	 * @param \TYPO3\Flow\Security\Exception\AuthenticationRequiredException $exception The exception thrown while the authentication process
	 * @return void
	 */
	protected function onAuthenticationFailure(\TYPO3\Flow\Security\Exception\AuthenticationRequiredException $exception = NULL) {
		$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Error('The entered username or password was wrong.', ($exception === NULL ? 1347016771 : $exception->getCode())));
		$this->redirect('index');
	}

	/**
	 * Is called if authentication was successful.
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	public function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
		if ($originalRequest !== NULL) {
			$this->redirectToRequest($originalRequest);
		}
		$this->redirect('index', 'Overview\Overview');
	}

	/**
	 * Logs out a - possibly - currently logged in account.
	 *
	 * @return void
	 */
	public function logoutAction() {
		parent::logoutAction();

		switch ($this->request->getFormat()) {
			#case 'extdirect' :
			#case 'json' :
			#	$this->view->assign('value',
			#		array(
			#			'success' => TRUE
			#		)
			#	);
			#	break;
			default :
				$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Notice('Successfully logged out.', 1318421560));
				$this->redirect('login');
			break;
		}
	}
}

?>