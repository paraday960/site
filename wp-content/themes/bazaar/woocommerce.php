<?php
/**
 * پوشش قالب بازار برای تمام صفحات ووکامرس
 * (فروشگاه، دسته‌بندی، محصول تکی، سبد خرید، پرداخت، حساب کاربری)
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

bz_shop_header();
?>
<div class="container" style="padding-bottom:52px">
	<?php
	if ( is_singular( 'product' ) ) {
		/* صفحه محصول تکی */
		woocommerce_content();
	} elseif ( is_cart() || is_checkout() || is_account_page() ) {
		/* صفحه‌های سبد خرید / پرداخت / حساب کاربری: محتوای برگه (شورت‌کد) */
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	} else {
		/* فروشگاه و آرشیو دسته‌بندی‌ها */
		woocommerce_content();
	}
	?>
</div>
<?php
get_footer();
