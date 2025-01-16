<?php

add_action( 'rest_api_init', function() {
  $namespace = 'wa/v1';

  register_rest_route($namespace, '/activities-types', [
    'methods' => 'GET',
    'callback' => 'get_activities_types',
  ]);

  register_rest_route($namespace, '/activities', [
    'methods' => 'GET',
    'callback' => 'get_activities',
    'type' => [
      'type'    => 'string',
      'required' => true,
    ],
  ]);

  register_rest_route($namespace, '/activities/(?P<id>\d+)', [
    'methods' => 'GET',
    'callback' => 'show_activity',
  ]);
});

function get_activities_types() {
  $terms = get_terms([
    'taxonomy' => 'activities_types',
    'hide_empty' => false,
    'orderby' => 'id',
    'order' => 'ASC',
  ]);
  return $terms;
}

function get_activities(WP_REST_Request $request) {
  $posts = get_posts([
    'posts_per_page' => -1,
    'post_type' => 'activities',
    'tax_query' => [
      [
        'taxonomy' => 'activities_types',
        'field' => 'term_id',
        'terms' => $request['type']
      ]
    ],
  ]);

  $data = [];

  foreach($posts as $post) {
    $period = get_field('period', $post->ID);

    $item = [
      'id' => $post->ID,
      'post_date' => $post->post_date,
      'post_content' => $post->post_content,
      'post_title' => $post->post_title,
      'post_name' => $post->post_name,
      'period' => $period,
    ];

    $thumbnailUrl = get_the_post_thumbnail_url($post);

    $previewWidth = 890;
    $previewHeight = 718;

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
  }

  return $data;
}

function show_activity(WP_REST_Request $request) {
  $id = (int) $request['id'];
  $post = get_post($id);
  $fields = get_fields($id);

  $imageId = get_post_thumbnail_id($post->ID);
  $image = null;

  if($imageId) {
    $res = wp_get_attachment_image_src($imageId, 'large');
    if($res) {
      $image = [
        'url' => $res[0],
        'width' => $res[1],
        'height' => $res[2],
      ];
    }
  }

  return [
    'id' => $post->ID,
    'title' => $post->post_title,
    'body' => $post->post_content,
    'date' => $post->post_date,
    'slug' => $post->post_name,
    'period' => $fields ? $fields['period'] : '',
    'image' => $image,
  ];
}
