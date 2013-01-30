<?php
namespace TYPO3\UserManagement\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "BKWI.Kernkaart".        *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Shows the name of the currently active user
 */
class AccountViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\Flow\Security\Context
	 * @Flow\Inject
	 */
	protected $securityContext;

	/**
	 * @param string $propertyPath
	 * @return string
	 */
	public function render($propertyPath = 'party.name') {
		$tokens = $this->securityContext->getAuthenticationTokens();

		foreach ($tokens as $token) {
			if ($token->isAuthenticated()) {
				return (string)\TYPO3\Flow\Reflection\ObjectAccess::getPropertyPath($token->getAccount(), $propertyPath);
			}
		}

		return '';
	}

}

?>