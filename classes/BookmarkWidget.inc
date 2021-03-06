<?php

class ComicPressBookmarkWidget extends WP_Widget {
	var $text_fields, $default_instance;

	// @codeCoverageIgnoreStart
	function ComicPressBookmarkWidget($skip_widget_init = false) {
		if (!$skip_widget_init) {
			$widget_ops = array('classname' => __CLASS__, 'description' => __('Allow the user to bookmark a page and then jump to it upon return.','comicpress') );
			$this->WP_Widget(__CLASS__, __('ComicPress Bookmark','comicpress'), $widget_ops);
		}

		$this->text_fields = array(
			'three-button' => array(
				'tag-page' => array(
					'label' => __('Tag page', 'comicpress'),
					'default' => __('Bookmark', 'comicpress')
				),
				'clear-tag-off' => array(
					'label' => __('Clear tag off', 'comicpress'),
					'default' => ''
				),
				'clear-tag-on' => array(
					'label' => __('Clear tag on', 'comicpress'),
					'default' => __('Clear', 'comicpress')
				),
				'goto-tag-off' => array(
					'label' => __('Goto tag off', 'comicpress'),
					'default' => ''
				),
				'goto-tag-on' => array(
					'label' => __('Goto tag on', 'comicpress'),
					'default' => __('Goto', 'comicpress')
				)
			),
			'one-button'   => array(
				'bookmark-clicker-off' => array(
					'label' => __('Set bookmark', 'comicpress'),
					'default' => __('+Bookmark', 'comicpress')
				),
				'bookmark-clicker-on' => array(
					'label' => __('Use bookmark', 'comicpress'),
					'default' => __('&gt;&gt;Bookmark', 'comicpress')
				)
			)
		);

		$this->default_instance = array(
			'title' => __('Bookmark This Page', 'comicpress'),
			'mode' => array_shift(array_keys($this->text_fields))
		);

		foreach (array_values($this->text_fields) as $fields) {
			foreach ($fields as $field => $info) {
				$this->default_instance[$field] = $info['default'];
			}
		}
	}

  function init() {}

  function is_active() {
  	add_action('template_redirect', array(&$this, 'template_redirect'));
  }

  function template_redirect() {
    wp_enqueue_script('prototype');
    wp_enqueue_script('cookiejar', plugin_dir_url(dirname(__FILE__)) . 'js/cookiejar.js', array('prototype'));
    wp_enqueue_script('bookmark', plugin_dir_url(dirname(__FILE__)) . 'js/bookmark.js', array('prototype', 'cookiejar'));
  }

  // @codeCoverageIgnoreEnd

  function form($instance) {
  	$instance = array_merge($this->default_instance, (array)$instance); ?>
  	<div id="<?php echo $this->get_field_id('wrapper') ?>">
  		<p>
  		  <label><?php _e('Title', 'comicpress') ?><br />
  		    <input class="widefat" type="text"
  		           name="<?php echo $this->get_field_name('title') ?>"
  		           value="<?php echo esc_attr($instance['title']) ?>" />
  		  </label>
	  	</p>
	  	<?php
	  	foreach (array(
	  		'three-button' => __('Three-button mode', 'comicpress'),
	  		'one-button' => __('One-button mode', 'comicpress')
	  	) as $mode => $label) { ?>
	  		<p>
	  		  <label>
	  		    <input type="radio"
	  		    			 id="<?php echo $this->get_field_id($mode) ?>"
	  		           name="<?php echo $this->get_field_name('mode') ?>"
	  		           value="<?php echo esc_attr($mode) ?>"
	  		           <?php echo $instance['mode'] == $mode ? 'checked="checked"' : '' ?> /> <?php echo $label ?>
	  		  </label>
	  		</p>
	  		<div id="<?php echo $this->get_field_id("${mode}-options") ?>"><p>
					<?php
						foreach ($this->text_fields[$mode] as $name => $info) {
							extract($info);
							$value = empty($instance[$name]) ? $default : $instance[$name];
							?>
								<label><?php echo $label ?><br />
									<input class="widefat" type="text"
									       name="<?php echo $this->get_field_name($name) ?>"
									       value="<?php echo esc_attr($value) ?>" />
								</label>
							<br /><?php
						}
					?>
	  		</p></div>
	  	<?php } ?>
  	</div>
  <?php }

  function update($new_instance, $old_instance) {
  	$instance = $this->default_instance;

  	$all_text_fields = array('title');
  	foreach ($this->text_fields as $type => $fields) {
  		$all_text_fields = array_merge($all_text_fields, array_keys($fields));
  	}

		foreach ($all_text_fields as $key) {
			if (isset($new_instance[$key])) {
				$instance[$key] = strip_tags($new_instance[$key]);
			}
		}

		if (isset($new_instance['mode'])) {
			if (isset($this->text_fields[$new_instance['mode']])) {
				$instance['mode'] = $new_instance['mode'];
			}
		}

		return $instance;
  }

  function widget($args, $instance) {
  	global $post;
  	extract($args, EXTR_SKIP);

  	$instance = (array)$instance;
  	$instance = array_merge($this->default_instance, $instance);

		echo $before_widget;

		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		if (!empty($title)) { echo $before_title . $title . $after_title; };

		$mode = $instance['mode'];

		$link = is_home() ? get_bloginfo('url') : get_permalink($post);

  	$id = 'comic-bookmark-' . md5(rand());
    switch ($instance['mode']) {
      case 'three-button': ?>
		    <div class="bookmark-widget three-button" id="<?php echo $id ?>">
      		<a href="#" class="tag-page"></a>
      		<a href="#" class="goto-tag"></a>
      		<a href="#" class="clear-tag"></a>
      	</div>
				<?php break;
			case 'one-button': ?>
		    <div class="bookmark-widget one-button" id="<?php echo $id ?>">
      		<a href="#" class="bookmark-clicker"></a>
      	</div>
				<?php break;
	 	} ?>
	  <script type="text/javascript">
			(function() {
				ComicBookmark.setup('<?php echo $id ?>', '<?php echo $mode ?>', '<?php echo $link ?>', {
					<?php
						$elements = array();
						foreach (array_keys($this->text_fields[$mode]) as $field) {
							$elements[] = "'{$field}': '{$instance[$field]}'";
						}
						echo implode(',', $elements);
					?>
				});
			}());
	  </script><?php
		echo $after_widget;
  }
}
