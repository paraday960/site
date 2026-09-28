<?php
/**
 * فرم جست‌وجو — پیش‌فرض روی محصولات
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<form role="search" method="get" class="bz-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="bz-s-<?php echo esc_attr( uniqid() ); ?>">جست‌وجو</label>
	<input type="search" class="search-field" placeholder="جست‌وجوی محصول…" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button type="submit" class="search-submit"><?php echo bz_icon( 'search', 17 ); ?> جست‌وجو</button>
</form>
