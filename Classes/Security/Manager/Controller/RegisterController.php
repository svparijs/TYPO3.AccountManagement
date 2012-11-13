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

}

?>