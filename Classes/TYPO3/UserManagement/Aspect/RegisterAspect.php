<?php
namespace TYPO3\UserManagement\Aspect;

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
 * @Flow\Aspect
 */
class RegisterAspect {

	/**
	* @var \TYPO3\Flow\Log\LoggerInterface A logger implementation
	*/
	protected $systemLogger;

	/**
	 * For logging we need a logger, which we will get injected automatically by
	 * the Object Manager
	 *
	 * @param \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger The System Logger
	 * @return void
	 */
	public function injectSystemLogger(\TYPO3\Flow\Log\SystemLoggerInterface $systemLogger) {
			$this->systemLogger = $systemLogger;
	}

	/**
	 * Intercept the Action request and check if the Settings.yaml contains a Register: TRUE *
	 * @Flow\Around("!setting(TYPO3.UserManagement.register) && method(TYPO3\UserManagement\Controller\.*->(register|create)Action())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return void
	 */
	public function anonymousRegistrationDisallowedBySettingsAdvice(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$logMessage = 'The new method of ' . $joinPoint->getMethodName() . ' in class ' . $joinPoint->getClassName() . ' has been called.';
		$this->systemLogger->log($logMessage);
		// TODO: Redirect
		return 'Registration is disabled';
	}

	/**
	 * Intercept the Action request and check if the Settings.yaml contains a Register: TRUE *
	 * @Flow\After("method(TYPO3\UserManagement\.*->create.*())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return void
	 */
	public function accountCreationRedirectToAdvice(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {

		$logMessage = 'The new method of ' . $joinPoint->getMethodName() . ' in class ' . $joinPoint->getClassName() . ' has been called.';
		$this->systemLogger->log($logMessage);

		//$joinPoint->redirect('index','Login');
	}
}
?>