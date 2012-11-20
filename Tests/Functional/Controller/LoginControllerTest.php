<?php
namespace Security\Manager\Tests\Functional\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Security.Manager".			*
 *                                                                        *
 *                                                                        */

/**
 * Testcase for method security of the backend controller
 *
 * @group large
 */
class LoginControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * We need to enable this, so that the database is set up. Otherwise
	 * there will be an error along the lines of:
	 *  "Table 'functional_tests.domain' doesn't exist"
	 *
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @var boolean
	 */
	protected $testableSecurityEnabled = TRUE;

	/**
	 * @var boolean
	 */
	protected $testableHttpEnabled = TRUE;

	/**
	 * @test
	 */
	public function indexActionIsGrantedForAdministrator() {
		$user = new User();
		$user->getPreferences()->set('context.workspace', 'user-admin');

		$account = $this->authenticateRoles(array('Administrator'));
		$account->setParty($user);
		$this->browser->request('http://localhost/login/index');
	}

	/**
	 * @test
	 */
	public function signedInActionIsDeniedForEverybody() {
		$this->browser->request('http://localhost/login/signedin');
		$this->assertSame(403, $this->browser->getLastResponse()->getStatusCode());
	}
}

?>