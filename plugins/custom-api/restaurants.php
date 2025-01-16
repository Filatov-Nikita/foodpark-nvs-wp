<?php

add_action( 'rest_api_init', function() {
  $namespace = 'wa/v1';

  register_rest_route($namespace, '/restaurants', [
    'methods' => 'GET',
    'callback' => 'get_restaurants',
  ]);
});

function get_restaurants() {
  $posts = get_posts([
    'post_type' => 'restaurants'
  ]);

  $data = [];

  foreach($posts as $post) {
    $item = [
      'id' => $post->ID,
      'title' => $post->post_title,
      'slug' => $post->post_name,
      'date' => $post->post_date,
    ];
    $terms = get_the_terms($post->ID, 'kitchen');

    if($terms) {
      $term = array_pop($terms);
      $item['kitchen'] = [
        'name' => $term->name,
        'slug' => $term->slug,
      ];
    } else {
      $item['kitchen'] = null;
    }

    $thumbnailUrl = get_the_post_thumbnail_url($post);

    $previewWidth = 890;
    $previewHeight = 712;

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

    $logo = get_field('логотип', $post->ID);

    $logoWidth = 150;
    $logoHeight = 150;

    $logoUrl = kama_thumb_src([
      'url' => $logo ? $logo['url'] : false,
      'w' => $logoWidth,
      'h' => $logoHeight,
    ]);

    $item['logo'] = [
      'url' => $logoUrl,
      'width' => $logoWidth,
      'height' => $logoHeight,
    ];

    $data[] = $item;
  }

  return $data;
}
