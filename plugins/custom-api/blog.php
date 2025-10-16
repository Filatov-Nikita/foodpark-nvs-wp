<?php

const CHANNEL_NAME = 'foodparkaura';
const DZEN_URL = 'https://dzen.ru/api/web/v1/export?country_code=ru&lang=ru&referrer_place=layout&content_type=article&channel_version=welcome_infinity&sort_type=regular&channel_name=';

add_action( 'rest_api_init', function() {
  $namespace = 'wa/v1';

  register_rest_route($namespace, '/blog', [
    'methods' => 'GET',
    'callback' => 'get_items',
  ]);
});

function is_cache_valid($updatedAt) {
  $now = time();
  $diff = $now - $updatedAt;
  return $diff < 60 * 15;
}

function data_mapper($items) {
  return array_map(function($item) {
    return [
      'id' => $item['id'],
      'title' => $item['title'],
      'text' => $item['text'],
      'link' => $item['shareLink'],
      'image' => $item['image'],
      'created_at' => $item['publicationDate'],
    ];
  }, $items);
}

function get_items() {
    $query = new WP_Query([
      'post_type' => 'page',
      'name' => 'blog',
    ]);

    $post = $query->post;

    if(!$post) {
      return new WP_Error('no_blog_page', 'Blog не найден', [ 'status' => 404 ]);
    };

    $content = get_field('dzen_json', $post->ID);
    $updatedAt = get_field('updated_at', $post->ID);

    if($content !== '' && $updatedAt !== '' && is_cache_valid($updatedAt)) {
      $data = json_decode($content, true);
      return $data;
    } else {
      $url = DZEN_URL . CHANNEL_NAME;

      $options = [
        'http' => [ 'method' => 'GET' ],
      ];

      $context = stream_context_create($options);

      $result = file_get_contents($url, false, $context);

      if(!$result) return new WP_REST_Response('Не удалось получить данные от dzen', 500);
      if(!is_string($result)) return new WP_REST_Response('Некорректные данные от dzen', 500);

      $data = json_decode($result, true);

      $items = data_mapper($data['feedData']['items']);

      $newPostData = wp_slash(json_encode($items));

      update_field('dzen_json', $newPostData, $post->ID);
      update_field('updated_at', time(), $post->ID);

      return $items;
    }
}
