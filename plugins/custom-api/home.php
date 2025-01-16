<?php

add_action( 'rest_api_init', function() {
  $namespace = 'wa/v1';

  register_rest_route($namespace, '/home', [
    'methods' => 'GET',
    'callback' => 'get_home',
  ]);
});

function get_home() {
  $query = new WP_Query([
    'post_type' => 'page',
    'name' => 'home',
  ]);

  if(!$query->post) {
    return new WP_Error('no_home_page', 'Главная не найдена', [ 'status' => 404 ]);
  };

  $fields = get_fields($query->post->ID);

  if(!$fields) {
    return new WP_Error('no_fields_page', 'Параметры страницы не найдены', [ 'status' => 404 ]);
  }

  $res = [
    'zagolovok' => $fields['zagolovok'],
    'podzagolovok' => $fields['podzagolovok'],
    'tekst_na_knopke' => $fields['tekst_na_knopke'],
    'vremya_raboty' => $fields['vremya_raboty'],
    'ssylka_na_telegram' => $fields['ssylka_na_telegram'],
    'address' => $fields['address'],
    'foodpark_map_href' => $fields['foodpark_map_href'],
    'phone' => $fields['phone'],
    'grid' => [
      'xl' => [
        'width' => $fields['big_photo']['width'],
        'height' => $fields['big_photo']['height'],
        'url' => $fields['big_photo']['url'],
      ],
      'sm' => [
        'width' => $fields['small_photo']['width'],
        'height' => $fields['small_photo']['height'],
        'url' => $fields['small_photo']['url'],
      ],
    ],
    'banner_zagolovok' => $fields['banner_zagolovok'],
    'banners' => [
      [
        'title' => $fields['podpis_1'],
        'preview' => create_banner($fields['foto_1']),
        'href' => $fields['ssylka_1'],
      ],
      [
        'title' => $fields['podpis_2'],
        'preview' => create_banner($fields['foto_2']),
        'href' => $fields['ssylka_2'],
      ],
      [
        'title' => $fields['podpis_3'],
        'preview' => create_banner($fields['foto_3']),
        'href' => $fields['ssylka_3'],
      ],
    ],
    'video' => [
      'url' => $fields['video']['url'],
      'width' => $fields['video']['width'],
      'height' => $fields['video']['height'],
    ],
  ];

  return $res;
}


function create_banner($image) {
  $previewWidth = 890;
  $previewHeight = 744;

  $previewUrl = kama_thumb_src([
    'url' => $image ? $image['url'] : false,
    'w' => $previewWidth,
    'h' => $previewHeight,
  ]);

  return [
    'url' => $previewUrl,
    'width' => $previewWidth,
    'height' => $previewHeight,
  ];
}
