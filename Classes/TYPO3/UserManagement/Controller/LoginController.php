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
 * A controller which allows for loggin into a application
 *
 * @Flow\Scope("singleton")
 */
class LoginController extends \TYPO3\Flow\Security\Authentication\Controller\AbstractAuthenticationController {

	/**
	 * @var array
	 */
	protected $viewFormatToObjectNameMap = array(
		'html'  => 'TYPO3\Fluid\View\TemplateView',
		'json'  => 'TYPO3\Flow\Mvc\View\JsonView',
		'jsonp' => 'TYPO3\UserManagement\View\TemplateView');

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	/**
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 * @Flow\Inject
	 */
	protected $authenticationManager;

	/**
	 * Index action
	 *
	 * @param string $username
	 * @return void
	 */
	public function indexAction($username = NULL) {
		if ($this->authenticationManager->isAuthenticated()) {
			if(isset($this->settings['Redirect']['signedIn'])) {
				$redirect = $this->settings['Redirect']['signedIn'];
				$this->redirect($redirect['actionName'], $redirect['controllerName'], $redirect['packageKey']);
			}
			$this->redirect('signedIn');
		}
		$this->view->assign('username', $username);
		$this->view->assign('hostname', $this->request->getHttpRequest()->getBaseUri()->getHost());
		$this->view->assign('date', new \DateTime());
	}

	/**
	 * Loginpanel action
	 *
	 * @param string $username
	 * @return void
	 */
	public function loginPanelAction($username = NULL) {
		$this->view->assign('username', $username);
		$this->view->assign('hostname', $this->request->getHttpRequest()->getBaseUri()->getHost());
		$this->view->assign('date', new \DateTime());
	}

	/**
	 *
	 * @return void
	 */
	public function signedInAction(){
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
	 * Is called if authentication was successful.
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $originalRequest The request that was intercepted by the security framework, NULL if there was none
	 * @return string
	 */
	public function onAuthenticationSuccess(\TYPO3\Flow\Mvc\ActionRequest $originalRequest = NULL) {
		$uriBuilder = $this->controllerContext->getUriBuilder();
		if ($originalRequest !== NULL) {
			$uriBuilder->uriFor($originalRequest->getControllerActionName(), NULL, $originalRequest->getControllerName(), $originalRequest->getControllerPackageKey());
		} else {
			if(isset($this->settings['Redirect']['signedIn'])) {
				$packageKey     = $this->settings['Redirect']['signedIn']['packageKey'];
				$controllerName = $this->settings['Redirect']['signedIn']['controllerName'];
				$actionName     = $this->settings['Redirect']['signedIn']['actionName'];
				$uri = $uriBuilder->uriFor($actionName, NULL, $controllerName, $packageKey);
			} else {
				$uri = $uriBuilder->uriFor('signIn', NULL, 'Login', 'TYPO3.UserManagement');
			}
		}

		$response = array();
		$response['status'] = 'OK';
		$response['redirect'] = $uri;

		$this->view->assign('value', $response);
	}

	/**
	 * Logs out a - possibly - currently logged in account.
	 *
	 * @return void
	 */
	public function logoutAction() {
		parent::logoutAction();

		switch ($this->request->getFormat()) {
			default :
				$this->flashMessageContainer->addMessage(new \TYPO3\Flow\Error\Message('Successfully logged out.', 1318421560));
				$this->redirect('index');
			break;
		}
	}

	/**
	 * Collects the errors and serves them
	 *
	 * @return void
	 */
	protected function errorAction() {
			// Create response array
		$response = array();
		$response['status'] = 'FAILED';
		$response['errors'] = $this->flashMessageContainer->getMessagesAndFlush();
		$this->view->assign('value', $response);
	}

	/**
	 *
	 * @return void
	 */
	public function callActionMethod() {
		if ($this->request->getFormat() === 'jsonp') {
				// @todo cleanup
			parent::callActionMethod();

			$content = $this->response->getContent();
			$content = str_replace(array("\n", "\r", "\t"), '', $content);

			// Added the if for debugging
			if ($this->request->hasArgument('callback')) {
				if ( !isset($content['response'])) {
					$this->response->setContent(sprintf(
						'%s(%s)',
						$this->request->getArgument('callback'),
						json_encode((object)array(
							'html' => $content
						))
					));
				}
			} else {
				if ( !isset($content['response'])) {
					$this->response->setContent(sprintf(
						'(%s)',
						json_encode((object)array(
							'html' => $content
						))
					));
				}
			}
		} else {
			parent::callActionMethod();
		}
	}

}

?>