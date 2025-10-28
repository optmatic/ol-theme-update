<?php
/**
 * Header template part with main container opener.
 *
 * @since   1.0.0
 *
 * @package The7\Templates
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'presscore_before_main_container' ); ?>

<?php if ( presscore_is_content_visible() ) : ?>

<div id="main" <?php presscore_main_container_classes(); ?>>

	<?php do_action( 'presscore_main_container_begin' ); ?>

	<div class="main-gradient"></div>
	<div class="wf-wrap">
	<div class="wf-container-main">

	<?php do_action( 'presscore_before_content' ); ?>
		
		<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-0QB9K9MXCC"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-0QB9K9MXCC');
</script>
		
<?php endif ?>
