<?php

add_action( 'rest_api_init', function() {
  $namespace = 'wa/v1';

  register_rest_route($namespace, '/restaurants', [
    'methods' => 'GET',
    'callback' => 'get_restaurants',
  ]);

  register_rest_route($namespace, '/restaurants/(?P<id>\d+)', [
    'methods' => 'GET',
    'callback' => 'show_restaurant',
  ]);

  register_rest_route($namespace, '/kitchens', [
    'methods' => 'GET',
    'callback' => 'get_kitchens',
  ]);
});

function get_kitchens() {
  $types = get_categories([
    'taxonomy' => 'kitchen',
    'hide_empty' => 1,
    'child_of' => 0,
    'posts_per_page' => -1,
  ]);

  return array_map(function ($item) {
    return [
      'id' => $item->term_id,
      'name' => $item->name,
      'slug' => $item->slug,
    ];
  }, $types);
}

function show_restaurant(WP_REST_Request $request) {
  $id = (int) $request['id'];
  $post = get_post($id);

  if(!$post) return new WP_REST_Response('not found', 404);

  $fields = get_fields($id);

  $item = [
    'id' => $post->ID,
    'title' => $post->post_title,
    'body' => trim(strip_shortcodes($post->post_content)),
    'slug' => $post->post_name,
    'gallery' => [],
  ];

  $item['kitchen'] = get_category_by_post($post->ID);

  $gallery = get_post_gallery($post->ID, false);

  if($gallery && $gallery['src']) {
    foreach($gallery['src'] as $url) {
      $width = 1350;
      $height = 900;

      $newUrl = kama_thumb_src([
        'url' => $url,
        'w' => $width,
        'h' => $height,
      ]);

      $item['gallery'][] = [
        'url' => $newUrl,
        'width' => $width,
        'height' => $height,
      ];
    }
  }

  if($fields) {
    $item['menu'] = $fields['menu'] ?? null;
  }

  return $item;
}

function get_restaurants() {

  $categoryId = isset($_GET['category_id']) ? sanitize_text_field($_GET['category_id']) : null;

  $posts = get_posts([
    'post_type' => 'restaurants',
    'posts_per_page' => -1,
    'tax_query' => $categoryId !== null ? [
      [
        'taxonomy' => 'kitchen',
        'field'    => 'id',
        'terms'    => $categoryId
      ]
    ] : false,
  ]);

  $data = [];

  foreach($posts as $post) {
    $item = [
      'id' => $post->ID,
      'title' => $post->post_title,
      'slug' => $post->post_name,
      'date' => $post->post_date,
    ];

    $item['kitchen'] = get_category_by_post($post->ID);

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

function get_category_by_post($postId) {
  $terms = get_the_terms($postId, 'kitchen');

  if(!$terms) return null;

  $term = array_pop($terms);
  return [
    'name' => $term->name,
    'slug' => $term->slug,
  ];
}
