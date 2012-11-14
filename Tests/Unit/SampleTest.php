<?php
namespace Security\Manager\Tests\Unit;

/**
 * Sample Unit Tests
 * TODO: Remove when test base starts to be solid
 */
class SampleTest extends \TYPO3\Flow\Tests\UnitTestCase {

	protected $foo;

	/**
	 * Setup test
	 */
	public function setUp() {
		$this->foo = 'bar';
	}

	/**
	 * Tear down test
	 */
	public function tearDown() {
			// Some possible cleanup
		$this->foo = NULL;
	}

	/**
	 * @test
	 */
	public function fooIsSetCorrectlyInSetupMethod() {
		$this->assertEquals('bar', $this->foo);
	}

	/**
	 * @return array
	 */
	public function aSampleDataProvider() {
		return array(
			array('foo', 'bar'),
			array('bar', 'baz')
		);
	}

	/**
	 * @test
	 * @dataProvider aSampleDataProvider
	 */
	public function aDataProviderPassesArgumentsToTestCorrectly($value1, $value2) {
		$this->assertEquals(strlen($value1), strlen($value2));
	}
}

?>