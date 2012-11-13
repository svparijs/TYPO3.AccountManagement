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
	public function indexAction($username = NULL) {
		$this->view->assign('username', $username);
		$this->view->assign('hostname', $this->request->getHttpRequest()->getBaseUri()->getHost());
		$this->view->assign('date', new \DateTime());
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