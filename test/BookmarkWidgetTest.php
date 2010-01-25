<?php

require_once('PHPUnit/Framework.php');
require_once('MockPress/mockpress.php');
require_once(dirname(__FILE__) . '/../classes/BookmarkWidget.inc');

class BookmarkWidgetTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		_reset_wp();
	}

	function providerTestForm() {
		return array(
			array(
				array(), array(
					'//input[contains(@name, "mode") and @value="three-button" and @checked]' => true,
					'//input[contains(@name, "mode") and @value="one-button" and not(@checked)]' => true,
				'//input[contains(@name, "title") and @value=""]' => true,
				),
			),
			array(
				array('title' => 'Title', 'mode' => 'three-button'),
				array(
					'//input[contains(@name, "mode") and @value="three-button" and @checked]' => true,
					'//input[contains(@name, "mode") and @value="one-button" and not(@checked)]' => true,
				'//input[contains(@name, "title") and @value="Title"]' => true,
				),
			),
			array(
				array('title' => 'Another Title', 'mode' => 'one-button'),
				array(
					'//input[contains(@name, "mode") and @value="one-button" and @checked]' => true,
					'//input[contains(@name, "mode") and @value="three-button" and not(@checked)]' => true,
				'//input[contains(@name, "title") and @value="Another Title"]' => true,
				),
			),
		);
	}

	/**
	 * @dataProvider providerTestForm
	 */
	function testForm($instance, $expected_additional_xpath) {
		$w = new ComicPressBookmarkWidget();

		ob_start();
		$w->form($instance);
		$content = ob_get_clean();

		$this->assertTrue(($xml = _to_xml($content, true)) !== false);

		foreach ($expected_additional_xpath as $xpath => $value) {
			$this->assertTrue(_xpath_test($xml, $xpath, $value), $xpath);
		}
	}
}
