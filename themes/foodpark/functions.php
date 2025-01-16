<?php

add_action('admin_menu', function() {
  remove_menu_page('edit.php');
});

add_action('after_setup_theme', function() {
  add_theme_support('post-thumbnails');
});
