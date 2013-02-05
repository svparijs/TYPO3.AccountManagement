<?php
namespace TYPO3\UserManagement\Tests\Unit\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "BKWI.Kernkaart".        *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class AccountViewHelperTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Party\Domain\Model\Person
	 */
	protected $person;

	/**
	 * @var \TYPO3\Flow\Security\Account
	 */
	protected $account;

	public function setUp() {
		$this->person = new \TYPO3\Party\Domain\Model\Person();
		$this->person->setName(new \TYPO3\Party\Domain\Model\PersonName('Dhr.', 'John', '', 'Doe'));

		$this->account = new \TYPO3\Flow\Security\Account();
		$this->account->setAccountIdentifier('test');
		$this->account->setParty($this->person);
	}

	/**
	 * @test
	 */
	public function viewHelperRendersTheNameOfCurrentlyAuthenticatedParty() {
		$token = new \TYPO3\Flow\Security\Authentication\Token\UsernamePassword();
		$token->setAccount($this->account);
		$token->setAuthenticationStatus(\TYPO3\Flow\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);

		$mockSecurityContext = $this->getAccessibleMock('\TYPO3\Flow\Security\Context');
		$mockSecurityContext->expects($this->any())->method('getAuthenticationTokens')->will($this->returnValue(array($token)));

		$viewHelper = new \TYPO3\UserManagement\ViewHelpers\AccountViewHelper();
		$this->inject($viewHelper, 'securityContext', $mockSecurityContext);

		$this->assertEquals('Dhr. John Doe', $viewHelper->render());
	}
}

?>