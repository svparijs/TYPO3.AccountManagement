<?php
namespace TYPO3\UserManagement\Tests\Functional\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.UserManagement".  *
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
	public function routeReachesIndexAction() {
		$this->markTestIncomplete('Needs to be fixed');
		$this->browser->request('http://localhost/login/index');
		$this->assertSame(200, $this->browser->getLastResponse()->getStatusCode());
	}

	/**
	 * @test
	 */
	public function signedInActionIsDeniedForEverybody() {
		$this->markTestIncomplete('Needs to be fixed');
		$this->browser->request('http://localhost/login/signedin');
		$this->assertSame(403, $this->browser->getLastResponse()->getStatusCode());
	}
}

?>