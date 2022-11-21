<?php
/**
 * Plugin Name: Extend Block Example
 * Description: Example how to extend an existing Gutenberg block.
 * Author: Team Jazz, Liip AG
 * Author URI: https://liip.ch
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: extend-block-example
 * Domain Path: /languages/
 *
 * @package extend-block-example
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'enqueue_block_editor_assets', 'extend_block_example_enqueue_block_editor_assets' );

function extend_block_example_enqueue_block_editor_assets() {
    // Enqueue our script
    wp_enqueue_script(
        'extend-block-example-js',
        esc_url( plugins_url( '/dist/extend-block-example.js', __FILE__ ) ),
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
        '1.0.0',
        true // Enqueue the script in the footer.
    );
}


// functions.php
function wporg_block_wrapper( $block_content, $block ) {

	if ( $block['blockName'] === 'core/image' && isset($block['attrs']['spacing']) && !empty($block['attrs']['spacing'])) {
        $media_id   = $block['attrs']['id'];
        return get_choco_html($media_id);
    }

	return $block_content;
}

add_filter( 'render_block', 'wporg_block_wrapper', 10, 2 );



function getimagedata($id) {
	$imagePost 	= get_post($id);
	$alt 		= get_post_meta($imagePost->ID, '_wp_attachment_image_alt', TRUE);
    $meta 		= get_post_meta($imagePost->ID, '_wp_attachment_metadata', TRUE);
    $title 		= $imagePost->post_excerpt;
    $desc 		= $imagePost->post_content;
    $original 	= wp_get_attachment_image_src($imagePost->ID, 'original')[0];
    $large 		= wp_get_attachment_image_src($imagePost->ID, 'large')[0];
    $medium 	= wp_get_attachment_image_src($imagePost->ID, 'medium')[0];
	$mini_image = get_mini_image($id);

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