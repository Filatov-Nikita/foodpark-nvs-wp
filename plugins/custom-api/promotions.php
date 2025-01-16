<?php

add_action( 'rest_api_init', function() {
  $namespace = 'wa/v1';

  register_rest_route($namespace, '/promotions', [
    'methods' => 'GET',
    'callback' => 'get_promotions',
  ]);
});

function get_promotions() {
  $posts = get_posts([
    'post_type' => 'promotions',
  ]);

  $data = [];

  foreach($posts as $post) {
    $item = [
      'id' => $post->ID,
      'title' => $post->post_title,
      'slug' => $post->post_name,
      'body' => $post->post_content,
      'date' => $post->post_date,
    ];

    $thumbnailUrl = get_the_post_thumbnail_url($post);

    $previewWidth = 896;
    $previewHeight = 1256;

    $previewUrl = kama_thumb_src([
      'url' => $thumbnailUrl,
      'w' => $previewWidth,
      'h' => $previewHeight,
    ]);

    $item['preview'] = [
      'url' => $previewUrl,
      'width' => $previewWidth,
      'height' => $previewHeight,
    ];

    $data[] = $item;
  };

  return $data;
}
