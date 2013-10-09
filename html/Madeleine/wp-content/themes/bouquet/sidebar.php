<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package WordPress
 * @subpackage Bouquet
 */
?>

	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<div id="secondary-wrapper">

		<div id="search-area">
			<?php get_search_form(); ?>
		</div>
		<div id="secondary" class="widget-area" role="complementary">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div><!-- #secondary .widget-area -->

	</div><!-- #secondary-wrapper -->
	<?php endif; // end sidebar widget area ?>
<script type="text/javascript"><!--
    google_ad_client = "ca-pub-7681530794477874";
    /* Poesie-Lebenswert */
    google_ad_slot = "2961548547";
    google_ad_width = 728;
    google_ad_height = 90;
    //-->
</script>
<script type="text/javascript"
        src="//pagead2.googlesyndication.com/pagead/show_ads.js">
</script>