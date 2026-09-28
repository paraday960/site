<?php
/**
 * قالب صفحه ۴۰۴
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<div class="container bz-archive">
	<div class="bz-404">
		<b>۴۰۴</b>
		<h1>صفحه پیدا نشد!</h1>
		<p>متاسفانه صفحه‌ای که دنبالش بودید وجود ندارد یا حذف شده است.</p>
		<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:20px">
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">بازگشت به خانه</a>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a class="button alt" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">رفتن به فروشگاه</a>
			<?php endif; ?>
		</div>
		<div style="max-width:420px;margin:28px auto 0"><?php get_search_form(); ?></div>
	</div>
</div>
<?php get_footer(); ?>
