<?php
/*
Plugin Name: ComicPress Widgets
Plugin URI: http://comicpress.org/
Description: Helpful widgets for ComicPress and other WordPress systems.
Version: 0.1
Author: John Bintz
Author URI: http://comicpress.org/

Copyright 2010 John Bintz  (email : john@coswellproductions.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

class ComicPressWidgets {
	function init() {
		$available_widgets = array();

		if (($dh = opendir(dirname(__FILE__) . '/classes')) !== false) {
			while (($file = readdir($dh)) !== false) {
				if (strpos($file, '.inc') !== false) {
					$class_name = "ComicPress" . preg_replace('#\..*$#', '', $file);
					require_once(dirname(__FILE__) . '/classes/' . $file);
					register_widget($class_name);
					$widget = new $class_name(true);
					if (method_exists($widget, 'init')) {
						$widget->init();
					}

					if (is_active_widget(false, false, strtolower($class_name))) {
						$widget->is_active();
					}
				}
			}
			closedir($dh);
		}
	}
}

add_action('widgets_init', array('ComicPressWidgets', 'init'));
