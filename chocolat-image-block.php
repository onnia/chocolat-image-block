<?php
/**
 * Plugin Name: Chocolat image block
 * Description: Extend an existing Gutenberg image block with image chocolat wrapper.
 * Author: Onni Aaltonen
 * Author URI: https://onniaaltonen.com
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: chocolat-image-block
 * Domain Path: /languages/
 *
 * @package chocolat-image-block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'enqueue_block_editor_assets', 'extend_block_example_enqueue_block_editor_assets' );

function extend_block_example_enqueue_block_editor_assets() {
    // Enqueue our script
    wp_enqueue_script(
        'chocolat-image-block-js',
        esc_url( plugins_url( '/dist/chocolat-image-block.js', __FILE__ ) ),
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
        '1.0.0',
        true // Enqueue the script in the footer.
    );
}

add_action('wp_enqueue_scripts', 'enqueue_scripts', 20);

function enqueue_scripts() {
    wp_enqueue_script('lazyload', 'https://cdnjs.cloudflare.com/ajax/libs/vanilla-lazyload/8.6.0/lazyload.min.js');
    wp_enqueue_script(
        'chocolat-js',
        esc_url( plugins_url( '/dist/chocolat-image-block.js', __FILE__ ) ),
        [],
        '1.0.0',
        true,
    );
    //  get_stylesheet_directory_uri() . '/Chocolat-1.0.4/dist/js/chocolat.iife.js');
    // dd(get_stylesheet_directory_uri());
}


/**
 * @param string $block_content
 * @param array $block Block details.
 */
function wporg_block_wrapper( $block_content, $block ) {

	if ( $block['blockName'] === 'core/image' && isset($block['attrs']['chocolat']) && !empty($block['attrs']['chocolat'])) {
        $media_id   = $block['attrs']['id'];
        return get_choco_html($media_id);
    }

	return $block_content;
}

add_filter( 'render_block', 'wporg_block_wrapper', 10, 2 );


/**
 * @param int $media_id
 */
function get_choco_html($media_id) {

    $data       = getimagedata($media_id);
    $output     = '';
    $output     .= "<a class=' chocolat-image " . $data->col . " " . $data->vertical . "' href='" . $data->original . "' title='" . $data->title . "'>";
    $output     .= "<picture>";
    if ($data->col == 'col-md-12') {
      $data->medium = $data->original;
    }

    $output .= "<source data-srcset='" . $data->large . "' media=\"(max-width: 767px)\">";
    $output .= "<source data-srcset='" . $data->medium . "' media=\"(min-width: 768px)\">";
    $output .= "<img width='".$data->w."' height='".$data->h."' class='animated fadeIn ' alt='" . $data->alt . "' data-src='" . $data->medium . "' title='" . $data->title . "' src='" .  $data->mini_image ."' >";
    $output .= "</picture>";
    $output .= "</a>";
  
    return $output;
}

/** 
 * @param int $media_id
 */
function getimagedata($media_id) {
	$imagePost 	= get_post($media_id);
	$alt 		= get_post_meta($imagePost->ID, '_wp_attachment_image_alt', TRUE);
    $meta 		= get_post_meta($imagePost->ID, '_wp_attachment_metadata', TRUE);
    $title 		= $imagePost->post_excerpt;
    $desc 		= $imagePost->post_content;
    $original 	= wp_get_attachment_image_src($imagePost->ID, 'original')[0];
    $large 		= wp_get_attachment_image_src($imagePost->ID, 'large')[0];
    $medium 	= wp_get_attachment_image_src($imagePost->ID, 'medium')[0];
	$mini_image = get_mini_image($media_id);

    $w 			= $meta['width'];
    $h 			= $meta['height'];

    if ($w > $h) {
    	$aspect_ratio 	= round($w/$h, 2);
    	$aspect_ratio 	= str_replace('.', '-', $aspect_ratio);
    	$align 			= 'vertical';
    	$vertical 		= ' vertical aspect-ratio-' . $aspect_ratio;
    }
    else {
    	$aspect_ratio 	= round($h/$w, 2);
    	$aspect_ratio 	= str_replace('.', '-', $aspect_ratio);
    	$align 			= 'horizontal';
    	$vertical 		= ' horizontal aspect-ratio-' . $aspect_ratio;
    }


	return (object)[
		'align' 		=> $align,
		'aspect_ratio' 	=> $aspect_ratio,
		'vertical' 		=> $vertical,
        'col'           => 'col-md-12',
		'h' 			=> $h,
		'w' 			=> $w,
		'mini_image'	=> $mini_image,
		'medium'		=> $medium,
		'large' 		=> $large,
		'original' 		=> $original,
		'desc' 			=> $desc,
		'title' 		=> $title,
		'meta' 			=> $meta,
		'alt' 			=> $alt,
		'meta' 			=> $meta,
		'imagePost' 	=> $imagePost,
	];

}