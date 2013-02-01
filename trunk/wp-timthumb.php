<?php

/*
  Plugin Name: TimThumb Helper
  Plugin URI: http://code.google.com/p/wp-timthumb/
  Description: Helper for attachments and TimThumb PHP Image Resizer. <a href="http://www.binarymoon.co.uk/2012/02/complete-timthumb-parameters-guide/" target="_blank">Complete TimThumb Parameters Guide</a>
  Version: 1.0.0
  Author: Javier Prieto
  Author URI: http://code.google.com/p/wp-timthumb/
  License: GPL2+
 */

// Prevent loading this file directly
defined('ABSPATH') || exit;

class WP_Timthumb {

	private $dir;
	private $url;
	private $source_url = 'http://timthumb.googlecode.com/svn/trunk/timthumb.php';
	private $sizes;
	private $image_mime_types = array('image/gif', 'image/jpeg', 'image/png');
	private $custom_params = array('post_id', 'df', 'post_slug', 'limit', 'mime_type', 'object');

	public function __construct() {
		$this->dir = WP_CONTENT_DIR . '/uploads/tt/';
		$this->url = WP_CONTENT_URL . '/uploads/tt/timthumb.php';
		if (!file_exists($this->dir . 'timthumb.php')) {
			$this->install_wp_timthumb();
		}
		/* get all sizes avaliable */
		$this->sizes = get_intermediate_image_sizes();
		/* add full size */
		$this->sizes[] = 'full';
	}

	/**
	 * Crea el directorio de cache de TimThumb y descarga la ultima version
	 */
	private function install_wp_timthumb() {
		if (!is_dir($this->dir)) {
			mkdir($this->dir, 0777, true);
		}
		$is_copied = copy($this->source_url, $this->dir . 'timthumb.php');
		if (!$is_copied) {
			$timthumb_code = file_get_contents($this->source_url);
			$timthumb_core = fopen($this->dir . 'timthumb.php', 'w');
			fwrite($timthumb_core, $timthumb_code);
			fclose($timthumb_core);
		}
	}

	/**
	 * Devuelve los attachments adjuntos a un post
	 * @param array $params
	 * @return array
	 */
	public function get_post_attachments($params = array()) {

		if (isset($params['post_slug'])) {
			$params['post_id'] = get_page_by_path($params['post_slug'])->ID;

			if ($params['post_id'] == NULL) {
				return array();
			}
			unset($params['post_slug']);
		}

		$params['post_id'] = isset($params['post_id']) ? (int) $params['post_id'] : get_the_ID();
		$params['limit'] = isset($params['limit']) ? (int) $params['limit'] : -1;

		$args = array(
				'post_type' => 'attachment',
				'posts_per_page' => $params['limit'],
				'post_parent' => $params['post_id']
		);

		if (isset($params['mime_type'])) {
			$args['post_mime_type'] = $params['mime_type'];
		}
		$attachments = get_posts($args);

		$params['object'] = isset($params['object']) ? (bool) $params['object'] : TRUE;

		$result = array();
		if (!$params['object']) {
			foreach ($attachments as $item) {
				$result[] = $item->guid;
			}
		} else {
			$result = $attachments;
		}
		return $result;
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
		unset($params['post_id'], $params['object']);

		if (!$params['attachment_id'])
			return array();

		$attachment = get_post($params['attachment_id']);
		$attachment->thumbnail = $this->get_timthumb_src($params);
		return ($is_object) ? $attachment : $attachment->thumbnail;
	}

	/**
	 * Obtiene las imagenes de un post
	 * @param type $args
	 * @return object
	 */
	function get_post_images($params = array()) {
		// defaults

		$params['post_id'] = isset($params['post_id']) ? (int) $params['post_id'] : get_the_ID();
		$params['limit'] = isset($params['limit']) ? (int) $params['limit'] : -1;
		$is_object = isset($params['object']) ? (bool) $params['object'] : TRUE;
		$params['object'] = TRUE;
		$params['mime_type'] = $this->image_mime_types;

		$attachments = $this->get_post_attachments($params);

		unset($params['mime_type']);

		if (isset($params['featured']) && $params['featured'] === TRUE) {
			// Search for featured image
			$featured = $this->get_featured_image($params);
			if (!empty($featured)) {
				foreach ($attachments as $key => $_item) {
					if ($_item->ID == $featured_ID) {
						$_temp = $_item;
						unset($attachments[$key]);
						array_unshift($attachments, $_temp);
						break;
					}
					unset($_item);
				}
			}
		}

		$featured_ID = get_post_meta($params['post_id'], '_thumbnail_id', true);
		if (!empty($featured_ID)) {
			
		}

		// defaults

		unset($params['post_id'], $params['post_id'], $params['object'], $params['limit']);
		$params = array_merge(array('src' => ''), $params);
		if (isset($params['size'])) {
			$size = $params['size'];
		}
		$image = array();
		foreach ($attachments as &$_item) {
			if (isset($size)) {
				$params['size'] = $size;
			}
			$params['attachment_id'] = $_item->ID;
			$_image = $this->get_attachment_image($params);
			$params['src'] = $_image;
			unset($params['df']);
			$image[] = $_item->thumbnail = $this->get_timthumb_src($params);
		}
		unset($_item);
		$this->_attachments = $attachments;
		$this->_current_attachment = -1;
		$this->_attachment_count = count($this->_attachments);
		return ($is_object) ? $attachments : $image;
	}

	/**
	 * Devuelve la primera imagen del post
	 * @param array $params
	 * @return string/array
	 */
	public function get_first_image(&$params = array()) {

		$params['post_id'] = (isset($params['post_id']) && !empty($params['post_id'])) ? (int) $params['post_id'] : get_the_ID();

		if (isset($params['featured']) && $params['featured'] === TRUE) {
			// Search for featured image
			$featured = $this->get_featured_image($params);
			if (!empty($featured)) {
				return $featured;
			}
		}

		$is_object = isset($params['object']) ? (bool) $params['object'] : TRUE;
		$params['object'] = TRUE;
		$params['limit'] = 1;

		$attachments = $this->get_post_images($params);

		if (count($attachments) > 0)
			$attachments = $attachments[0];

		if (empty($attachments)) {
			if (isset($params['df'])) {
				$attachments = get_bloginfo('template_url') . $params['df'];
			} else {
				$attachments = '';
			}
		} else {
			$params['attachment_id'] = $attachments->ID;
			$attachments->thumbnail = $this->get_timthumb_src($params);
		}

		if ($is_object) {
			return $attachments;
		} else {
			return (is_object($attachments)) ? $attachments->thumbnail : $attachments;
		}
	}

	/**
	 * <pre>
	 * 'size',<br>
	 * 'attachment_id',<br>
	 * 'h',<br>
	 * 'w',<br>
	 * </pre>
	 * @param array $params
	 * @return string
	 */
	function get_attachment_image(&$params) {
		$size = $this->get_size_param($params);
		$temp = wp_get_attachment_image_src($params['attachment_id'], $size);
		unset($params['attachment_id']);
		return $temp[0];
	}

	/**
	 * <pre>
	 * 'src',<br>
	 * 'h',<br>
	 * 'w',<br>
	 * </pre>
	 * @param array $params
	 * @return string
	 */
	function get_timthumb_src($params) {

		if (isset($params['attachment_id'])) {
			$params['src'] = $this->get_attachment_image($params);
		}
		if (isset($params['h']) or isset($params['w'])) {
			// Remove unused params
			foreach ($this->custom_params as $item) {
				unset($params[$item]);
			}
			$src = $this->url . '?';
			$_src[] = 'src=' . $params['src'];
			unset($params['src']);
			foreach ($params as $key => $value) {
				$_src[] = "{$key}={$value}";
			}
			$src .= implode('&amp;', $_src);
			return $src;
		} else {
			return $params['src'];
		}
	}

	private function get_size_param($params) {
		$size = null;
		if (isset($params['size']) && in_array($params['size'], $this->sizes)) {
			$size = $params['size'];
		} else {
			if (isset($params['h']) && (int) $params['h'] > 0)
				$size[] = (int) $params['h'];

			if (isset($params['w']) && (int) $params['w'] > 0)
				$size[] = (int) $params['w'];
		}
		return empty($size) ? 'large' : $size;
	}

}

global $tt;
$tt = !is_object($tt) ? null : $tt;

/**
 * Devuelve la primera imagen del post, por defecto trae la imagen destacada
 * si el par&aacute;metro <i>object</i> es falso devolvera el string con la url de la imagen,
 * de lo contrario devuelve el objeto
 * @param array $params
 * @return string|array
 * @since 2.0
 */
function get_first_image($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	# defaults
	$params['object'] = isset($params['object']) ? (bool) $params['object'] : TRUE;
	$params['featured'] = isset($params['featured']) ? (bool) $params['featured'] : TRUE;
	$image = $tt->get_first_image($params);
	return $image;
}

/**
 * Muestra la primera imagen del post
 * @param array $params
 * @since 2.1.1
 */
function the_first_image($params = array()) {
	# override object param
	$params['object'] = FALSE;
	echo get_first_image($params);
}

/**
 * Devuelve la imagen destacada del post
 * @global WP_Timthumb $tt
 * @param array $params
 * @since 2.1.1
 * @return object|string
 */
function get_featured_image($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_featured_image($params);
}

/**
 * Muestra la url de la imagen destacada
 * @param array $params
 * @since 2.1.1
 */
function the_featured_image($params = array()) {
	$params['object'] = FALSE;
	echo get_featured_image($params);
}

/**
 * Devuelve las im&aacute;genes del post
 * si el par&aacute;metro <i>object</i> es falso devolvera un arreglo con los string con la url de la imagen,
 * de lo contrario devuelve un arreglo de objetos
 * @param array $params
 * @return array
 * @since 2.0
 */
function get_post_images($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_post_images($params);
}

function get_timthumb_image($params) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	return $tt->get_timthumb_src($params);
}

function timthumb_image($params) {
	echo get_timthumb_image($params);
}

/**
 * Devuelve los adjuntos de un post
 * @global WP_Timthumb $tt
 * @param array $params
 * @return array
 * @since 2.1.1
 */
function get_post_attachments($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();

	return $tt->get_post_attachments($params);
}

/**
 * Devuelve un string con la url del adjunto
 * @param array $params
 * @return string
 * @since 2.1.1
 */
function get_post_first_attachment($params = array()) {
	# override limit param
	$params['limit'] = 1;
	# override object param
	$params['object'] = FALSE;
	$attachment = get_post_attachments($params);
	if (count($attachment) > 0) {
		return $attachment[0];
	} else {
		return '';
	}
}

/**
 * Muestra un string con la url del primer adjunto
 * @param array $params
 * @since 2.1.1
 */
function the_first_attachment($params = array()) {
	echo get_first_attachment($params);
}

/**
 * Devuelve todas las imagenes adjuntas post
 * @global WP_Timthumb $tt
 * @param type $params
 * @return type
 */
function get_all_images($params = array()) {
	global $tt;
	if (!is_object($tt))
		$tt = new WP_Timthumb();
	$params['limit'] = isset($params['limit']) ? $params['limit'] : -1;
	return $tt->get_post_images($params);
}

