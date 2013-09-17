<?php
namespace TYPO3\AccountManagement\ViewHelpers;

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