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
				'//input[contains(@name, "title") and @value="Bookmark This Page"]' => true,
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

	function providerTestUpdate() {
		return array(
			array(
				array(),
				array()
			),
			array(
				array('tag-page' => 'Test', 'title' => 'Test title', 'mode' => 'one-button'),
				array('tag-page' => 'Test', 'title' => 'Test title', 'mode' => 'one-button')
			),
			array(
				array('mode' => 'two-button'),
				array('mode' => 'three-button')
			),
		);
	}

	/**
	 * @dataProvider providerTestUpdate
	 */
	function testUpdate($update_array, $expected_instance_merge) {
		$w = new ComicPressBookmarkWidget();

		$this->assertEquals(array_merge($w->default_instance, $expected_instance_merge), $w->update($update_array, array()));
	}

	function providerTestWidget() {
		return array(
			array(
				array(
					'title' => 'Title',
					'mode'  => 'one-button',
					'tag-page' => 'Tag page',
					'bookmark-clicker-off' => 'Clicker off'
				),
				array(
					'//p[text()="Title"]' => true,
					'//div[@class="bookmark-widget"]/a[@class="bookmark-clicker"]' => true,
					'//script[contains(text(), "tag-page")]' => false,
					'//script[contains(text(), "bookmark-clicker-off")]' => true,
					'//script[contains(text(), "Post url")]' => true,
				)
			),
			array(
				array(
					'title' => 'Other Title',
					'mode'  => 'three-button',
					'tag-page' => 'Tag page',
					'bookmark-clicker-off' => 'Clicker off'
				),
				array(
					'//p[text()="Other Title"]' => true,
					'//div[@class="bookmark-widget"]/a[@class="tag-page"]' => true,
					'//script[contains(text(), "tag-page")]' => true,
					'//script[contains(text(), "bookmark-clicker-off")]' => false,
					'//script[contains(text(), "Blog url")]' => true,
				),
				true
			),
		);
	}

	/**
	 * @dataProvider providerTestWidget
	 */
	function testWidget($instance, $expected_xpath, $is_home = false) {
		global $post;

		$post = (object)array(
			'ID' => 1,
			'guid' => 'Post url'
		);

		wp_insert_post($post);

		$w = new ComicPressBookmarkWidget();

		_set_bloginfo('url', 'Blog url');
		_set_current_option('is_home', $is_home);

		ob_start();
		$w->widget(array(
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '<p>',
			'after_title' => '</p>'
		), $instance);
		$content = ob_get_clean();

		$this->assertTrue(($xml = _to_xml($content, true)) !== false);

		foreach ($expected_xpath as $xpath => $value) {
			$this->assertTrue(_xpath_test($xml, $xpath, $value), $xpath);
		}
	}
}
