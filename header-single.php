<?php
/**
 * Template part with actual header.
 *
 * @since 1.0.0
 *
 * @package The7\Templates
 */

defined( 'ABSPATH' ) || exit;

?><!DOCTYPE html>
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?> class="no-js">
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<?php if ( presscore_responsive() ) : ?>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<?php endif ?>
	<?php presscore_theme_color_meta(); ?>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<?php
	presscore_js_resize_event_hack();
	wp_head();
	?>
	    	<link rel="preload" href="https://optimiselearning.com/wp-content/themes/dt-the7/fonts/icomoon-the7-font/icomoon-the7-font.woff?wi57p5" as="font" type="font/ttf" crossorigin="anonymous">
	
	    	<link rel="preload" href="https://optimiselearning.com/wp-content/themes/dt-the7/fonts/icomoon-the7-font/icomoon-the7-font.ttf?wi57p5" as="font" type="font/ttf" crossorigin="anonymous">
	
<!-- recaptcha tag -->
	    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	
<!-- product/brand schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "Optimise Learning",
  "image": "https://optimiselearning.com/wp-content/uploads/2020/12/optimiselearningDEC81.png",
  "description": "Optimise Learning provides Australian students with high-quality, individualised online tutoring lessons regardless of where they live.",
  "brand": {
    "@type": "Brand",
    "name": "Optimise Learning"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "5",
    "reviewCount": "39",
    "bestRating": "5",
    "worstRating": "1"
  }
}
</script>

</head>
<body id="the7-body" <?php body_class(); ?>>
<?php
do_action( 'presscore_body_top' );

$config = presscore_config();

$page_class = '';
if ( 'boxed' === $config->get( 'template.layout' ) ) {
	$page_class = 'class="boxed"';
}
?>

<div id="page" <?php echo $page_class; ?>>
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'the7mk2' ); ?></a>
<?php
if ( apply_filters( 'presscore_show_header', true ) ) {
	presscore_get_template_part( 'theme', 'header/header', str_replace( '_', '-', $config->get( 'header.layout' ) ) );
	presscore_get_template_part( 'theme', 'header/mobile-header' );
}

if ( presscore_is_content_visible() && $config->get( 'template.footer.background.slideout_mode' ) ) {
	echo '<div class="page-inner">';
}
	
?>
