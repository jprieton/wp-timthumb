<?php

/*
  Plugin Name: TimThumb Helper
  Plugin URI: http://code.google.com/p/wp-timthumb/
  Description: Helper for attachments and TimThumb PHP Image Resizer. <a href="http://www.binarymoon.co.uk/2012/02/complete-timthumb-parameters-guide/" target="_blank">Complete TimThumb Parameters Guide</a>
  Version: 1.1.1
  Author: Javier Prieto
  Author URI: http://code.google.com/p/wp-timthumb/
  License: GPL2+
 */

// Prevent loading this file directly
defined('ABSPATH') || exit;

class WP_Timthumb {

	/**
	 * Parámetros que se deben omitir al generar la url
	 * @var array
	 */
	private $custom_params = array(
			'attachment_id' => null,
			'default' => null,
			'featured' => null,
			'limit' => null,
			'mime_type' => null,
			'object' => null,
			'post' => null,
			'post__not_in' => null,
			'post_id' => null,
			'post_content' => null,
			'shortcode' => null,
			'size' => null,
			'slug' => null);

	/**
	 * Almacenamiento temporal de las galerias
	 * @var array
	 */
	private $galleries = array();

	/**
	 * Almacenamiento temporal de las galerias
	 * @var array
	 */
	private $galleries_temp = array();

	/**
	 * Almacenamiento temporal de los adjuntos
	 * @var array
	 */
	public $post_attachments = array();

	/**
	 *  Arreglo con los mime-type mas usados
	 * @var array
	 */
	private $mime_types = array(
			'image' => array('image/gif', 'image/jpeg', 'image/png'),
			'pdf' => array('application/pdf')
	);

	/**
	 * Directorio donde está la librería timthumb.php
	 * @var string
	 */
	private $lib_dir = '';

	/**
	 * URL para descargar la última versión de la librería TimThumb
	 * @var string
	 */
	//private $lib_src = 'http://timthumb.googlecode.com/svn/trunk/timthumb.php';
	private $lib_src = '';

	/**
	 * URL de la librería TimThumb a usar al construir la url del recorte
	 * @var type
	 */
	private $lib_url = '';

	public function __construct() {
		$this->lib_src = WP_PLUGIN_DIR . '/wp-timthumb/timthumb.php';
		$this->lib_dir = WP_CONTENT_DIR . '/uploads/tt/';
		$this->lib_url = WP_CONTENT_URL . '/uploads/tt/timthumb.php';
		if (!file_exists($this->lib_dir . 'timthumb.php')) {
			$this->install_wp_timthumb();
		}
	}

	/**
	 * Instala/actualiza la librería TimThumb
	 */
	private function install_wp_timthumb() {
		if (!is_dir($this->lib_dir)) {
			mkdir($this->lib_dir, 0777, true);
		}
		# Intentamos copiar directamente desde la url
		$is_copied = copy($this->lib_src, $this->lib_dir . 'timthumb.php');
		# Si no logra copiar directamente intentamos crear el archivo
		if (!$is_copied) {
			$tt_code = file_get_contents($this->lib_src);
			$tt_core = fopen($this->lib_dir . 'timthumb.php', 'w');
			fwrite($tt_core, $tt_code);
			fclose($tt_core);
		}
	}

	/**
	 * Devuelve un string tamaño predeterminado o un arreglo con las dimensiones
	 * @param array $params
	 * @return array/string
	 */
	private function get_size_param($params) {
		$size = null;
		if (empty($this->sizes)) {
			/* get all sizes avaliable */
			$this->sizes = get_intermediate_image_sizes();
			/* add full size */
			$this->sizes[] = 'full';
		}
		if (isset($params['size']) && in_array($params['size'], $this->sizes)) {
			$size = $params['size'];
		} else {
			if (isset($params['h']) && (int) $params['h'] > 0)
				$size[] = (int) $params['h'];

			if (isset($params['w']) && (int) $params['w'] > 0)
				$size[] = (int) $params['w'];

			if (count($size) != 2) {
				$size = 'large';
			}
		}
		return empty($size) ? 'large' : $size;
	}

	function get_attachment_image($params) {
		$size = $this->get_size_param($params);
		$temp = wp_get_attachment_image_src($params['attachment_id'], $size);
		unset($params['attachment_id']);
		return $temp[0];
	}

	/**
	 * Devuelve los attachments adjuntos a un post
	 * @param array $params
	 * @return array
	 */
	public function get_post_attachments($params = array()) {

		if (isset($params['slug'])) {
			$params['post_id'] = get_page_by_path($params['slug'])->ID;

			if ($params['post_id'] == NULL) {
				return array();
			}
			unset($params['post_slug']);
		}

		$params['post_id'] = isset($params['post_id']) ? (int) $params['post_id'] : get_the_ID();
		$params['limit'] = isset($params['limit']) ? (int) $params['limit'] : -1;

		# Parámetros base
		$args = array(
				'post_type' => 'attachment',
				'posts_per_page' => $params['limit'],
				'post_parent' => $params['post_id']
		);

		# Si se define la exclusión de adjuntos
		if (isset($params['post__not_in'])) {
			$args['post__not_in'] = (is_array($params['post__not_in'])) ? $params['post__not_in'] : array($params['post__not_in']);
		}

		# Si se define el tipo de adjunto
		if (isset($params['mime_type'])) {
			if (array_key_exists($params['mime_type'], $this->mime_types)) {
				$args['post_mime_type'] = $this->mime_types[$params['mime_type']];
			} else {
				$args['post_mime_type'] = $params['mime_type'];
			}
		}

		$this->post_attachments = get_posts($args);

		$params['object'] = isset($params['object']) ? (bool) $params['object'] : TRUE;

		$result = array();
		if (!$params['object']) {
			foreach ($this->post_attachments as $item) {
				$result[] = $item->guid;
			}
		} else {
			$result = $this->post_attachments;
		}
		return $result;
	}

	/**
	 * Obtiene las imagenes de un post
	 * @param type $args
	 * @return object
	 */
	function get_post_images($params = array()) {

		# Si no está definido el post_id usar el actual
		$params['post_id'] = isset($params['post_id']) ? (int) $params['post_id'] : get_the_ID();
		$params['limit'] = isset($params['limit']) ? (int) $params['limit'] : -1;
		$is_object = isset($params['object']) ? (bool) $params['object'] : TRUE;
		$params['object'] = TRUE;
		$params['mime_type'] = 'image';

		$featured = FALSE;

		if (isset($params['featured'])) {
			$params['featured'] = (bool) $params['featured'];
			$featured = $this->get_featured_image($params);

			if (!empty($featured)) {
				$params['limit'] = ($params['featured'] && $params['limit'] > 0) ? $params['limit'] - 1 : $params['limit'];
				$params['post__not_in'] = $featured->ID;
			}
		}

		$images = array();
		if ($params['limit'] > 0 or $params['limit'] === -1) {
			$images = $this->get_post_attachments($params);
		}

		$attachments = array();
		if ($featured && $params['featured'] === TRUE) {
			$attachments[] = $featured;
		}
		$attachments = array_merge($attachments, $images);

		$thumbnails = array();
		foreach ($attachments as &$_item) {
			$params['attachment_id'] = $_item->ID;
			$params['src'] = (isset($params['size']) && $params['size'] == 'full') ? $_item->guid : $this->get_attachment_image($params);
			$thumbnails[] = $_item->thumbnail = $this->get_timthumb_src($params);
		}
		unset($_item);
		$this->post_attachments = $attachments;
		return ($is_object) ? $attachments : $thumbnails;
	}

	/**
	 * Devuelve la URL con los parámetros para el recorte de la imagen con TimThumb
	 * @param array $params
	 * @return string
	 */
	function get_timthumb_src($params) {

		if (!isset($params['src']) && !empty($this->post_attachments) && !isset($params['attachment_id'])) {
			$params['attachment_id'] = $this->post_attachments->ID;
		}

		if (isset($params['attachment_id'])) {
			$params['src'] = $this->get_attachment_image($params);
		}

		# Debe estar definido al menos el parametro src
		if (isset($params['src'])) {
			# Eliminar parametros innecesarios
			$params = array_diff_key($params, $this->custom_params);
			if (count($params) > 1) {
				# Crear un string con todos los parámetros
				foreach ($params as $key => $value) {
					$_src[] = "{$key}={$value}";
				}
				$src = $this->lib_url . '?' . implode('&amp;', $_src);
				return $src;
			} else {
				return $params['src'];
			}
		} else {
			return $params['src'];
		}
	}

	/**
	 * Devuelve la imagen destacada del post
	 * @param array $params
	 * @return array/string
	 */
	public function get_featured_image($params = array()) {
		// Set defaults
		$params['post_id'] = isset($params['post_id']) ? (int) $params['post_id'] : get_the_ID();
		$is_object = isset($params['object']) ? (bool) $params['object'] : TRUE;

		// Search attachment_id
		$params['attachment_id'] = get_post_meta($params['post_id'], '_thumbnail_id', true);

		if (!$params['attachment_id'])
			return array();

		$this->post_attachments[0] = get_post($params['attachment_id']);
		$this->post_attachments[0]->thumbnail = $this->get_timthumb_src($params);
		return ($is_object) ? $this->post_attachments[0] : $this->post_attachments[0]->thumbnail;
	}

	/**
	 * Return an array with galleries and WP_Post objects
	 * @global WP_Post $post
	 * @param array $params
	 * @return array
	 */
	public function get_post_galleries($params = array()) {

		$params['shortcode'] = isset($params['shortcode']) ? $params['shortcode'] : false;
		$params['post_id'] = isset($params['post']) ? (int) $params['post'] : false;
		$params['post'] = (isset($params['post']) && is_object($params['post'])) ? $params['post'] : false;
		$params['slug'] = isset($params['slug']) ? $params['slug'] : false;

		$content = '';

		if ($params['shortcode'] or $params['post_id'] or $params['post'] or $post['slug']) {

			if ($params['shortcode']) {
				$content = $params['shortcode'];
			} elseif (!is_object($params['post']) && $params['post_id']) {
				$params['post'] = get_post($params['post_id']);
				$content = $params['post']->post_content;
			} elseif ($params['slug']) {
				$params['post'] = get_page_by_path($params['slug']);
				$content = $params['post']->post_content;
			} else {
				$content = $params['post']->post_content;
			}
		} else {
			global $post;
			$params['post'] = $post;
			$content = $params['post']->post_content;
		}

		// Almacena el shortcode sin procesar en $this->galleries_temp;
		$this->get_galleries_shotcodes($content);

		if (count($this->galleries_temp) > 0) {
			foreach ($this->galleries_temp as $gallery) {
				$_ids = explode(',', $gallery['ids']);
				$_gallery = array();

				foreach ($_ids as $attachment_id) {
					$params['object'] = TRUE;
					$params['attachment_id'] = (int) $attachment_id;
					$item_gallery = get_post($attachment_id);

					if (empty($item_gallery))
						continue;

					$item_gallery->thumbnail = $this->get_timthumb_src($params);
					$_gallery[] = $item_gallery;
				}

				if (isset($gallery['orderby']) && $gallery['orderby'] == 'rand') {
					shuffle($_gallery);
				}
				$this->galleries[] = $_gallery;
			}
		}
		return $this->galleries;
	}

	/**
	 * Get galleries shortcodes from content
	 * @param string $content
	 * @return array
	 * @since 1.1.0
	 */
	private function get_galleries_shotcodes($content) {
		$pattern = get_shortcode_regex();
		return preg_replace_callback("/$pattern/s", array($this, 'store_gallery'), $content);
	}

	/**
	 * Store galleries in an array
	 * @param array $m
	 * @since 1.1.0
	 * @return string
	 */
	private function store_gallery($m) {
		if ($m[2] == "gallery") {
			$tag = $m[2];
			$attr = shortcode_parse_atts($m[3]);
			$this->galleries_temp[] = $attr;
		}
	}

}

global $tt;
$tt = !is_object($tt) ? null : $tt;

/**
 * Devuelve los adjuntos de un post
 * @global WP_Timthumb $tt
 * @param array $params
 * @return array
 */
function get_post_attachments($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_post_attachments($params);
}

/**
 * Devuelve las im&aacute;genes del post
 * si el par&aacute;metro <i>object</i> es falso devolvera un arreglo con los string con la url de la imagen,
 * de lo contrario devuelve un arreglo de objetos
 * @global WP_Timthumb $tt
 * @param array $params
 * @return array
 */
function get_post_images($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_post_images($params);
}

/**
 * Devuelve la imagen destacada del post
 * @global WP_Timthumb $tt
 * @param array $params
 * @return object|string
 */
function get_featured_image($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_featured_image($params);
}

/**
 * Displays the url of the post featured image
 * @param array $params
 */
function the_featured_image($params = array()) {
	# override object param
	$params['object'] = FALSE;
	$fi = get_featured_image($params);
	echo (empty($fi) && !empty($params['default'])) ? get_bloginfo('template_url') . '/' . $params['default'] : $fi;
}

/**
 * Devuelve la primera imagen del post, por defecto trae la imagen destacada,
 * si la imagen destacada no existe devuelve la primera imagen adjunta
 * @global WP_Timthumb $tt
 * @param array $params
 * @return string|array
 */
function get_first_image($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	# defaults
	$params['object'] = isset($params['object']) ? (bool) $params['object'] : TRUE;
	$params['featured'] = isset($params['featured']) ? (bool) $params['featured'] : TRUE;
	$params['limit'] = 1;
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	$first_image = $tt->get_post_images($params);
	return $first_image[0];
}

/**
 * Displays the url of the post first image (the featured image by default)
 * @param array $params
 */
function the_first_image($params = array()) {
	# override object param
	$params['object'] = FALSE;
	$fi = get_first_image($params);
	echo (empty($fi) && !empty($params['default'])) ? get_bloginfo('template_url') . '/' . $params['default'] : $fi;
}

/**
 * Devuelve el string de la url del recorte del TimThumb
 * @global WP_Timthumb $tt
 * @param array $params
 * @return string
 */
function get_timthumb_src($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_timthumb_src($params);
}

/**
 * Muestra la url de primera imagen del post (la imagen destacada por defecto)
 * @param array $params
 */
function the_timthumb_src($params = array()) {
	echo get_timthumb_src($params);
}

/**
 * Return an array of galleries from content
 * @global WP_Timthumb $tt
 * @param array $params
 * @return array
 * @since 1.1.0
 */
function get_post_galleries($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_post_galleries($params);
}

