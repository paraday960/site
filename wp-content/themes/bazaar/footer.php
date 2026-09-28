<?php
/**
 * فوتر قالب بازار
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
</main>

<footer class="bz-footer">
	<div class="container">
		<div class="bz-footer-main">

			<div>
				<div class="bz-footer-brand">
					<span class="bz-brand-logo"><?php echo bz_icon( 'cart', 22 ); ?></span>
					<b><?php bloginfo( 'name' ); ?></b>
				</div>
				<p class="bz-footer-about"><?php echo wp_kses_post( bz_get_option( 'footer_about' ) ); ?></p>
				<div class="bz-trust-badges">
					<span class="bz-trust-badge">ضمانت اصالت کالا</span>
					<span class="bz-trust-badge">پرداخت امن</span>
					<span class="bz-trust-badge">پشتیبانی ۲۴/۷</span>
				</div>
				<div class="bz-socials">
					<a href="#" aria-label="تلگرام"><?php echo bz_icon( 'telegram', 18 ); ?></a>
					<a href="#" aria-label="اینستاگرام"><?php echo bz_icon( 'instagram', 18 ); ?></a>
					<a href="#" aria-label="ایمیل"><?php echo bz_icon( 'mail', 18 ); ?></a>
				</div>
			</div>

			<div>
				<h4>دسترسی سریع</h4>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => '', 'fallback_cb' => false ) );
				} else {
					echo '<ul>';
					if ( class_exists( 'WooCommerce' ) ) {
						echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">فروشگاه</a></li>';
						echo '<li><a href="' . esc_url( wc_get_cart_url() ) . '">سبد خرید</a></li>';
						echo '<li><a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">حساب کاربری</a></li>';
					}
					echo '<li><a href="' . esc_url( home_url( '/blog/' ) ) . '">وبلاگ</a></li>';
					echo '</ul>';
				}
				?>
			</div>

			<div>
				<h4>خدمات مشتریان</h4>
				<ul>
					<li><a href="#">رویه ارسال سفارش</a></li>
					<li><a href="#">شرایط بازگشت کالا</a></li>
					<li><a href="#">راهنمای خرید</a></li>
					<li><a href="#">سوالات متداول</a></li>
					<li><a href="#">حریم خصوصی</a></li>
				</ul>
			</div>

			<div>
				<h4>تماس با ما</h4>
				<ul class="bz-footer-contact">
					<li><?php echo bz_icon( 'phone-m', 15 ); ?> <span><?php echo esc_html( bz_get_option( 'footer_phone' ) ); ?></span></li>
					<li><?php echo bz_icon( 'mail', 15 ); ?> <span><?php echo esc_html( bz_get_option( 'footer_email' ) ); ?></span></li>
					<li><?php echo bz_icon( 'pin', 15 ); ?> <span><?php echo esc_html( bz_get_option( 'footer_addr' ) ); ?></span></li>
				</ul>
			</div>

		</div>
	</div>

	<div class="bz-footer-bottom">
		<div class="container">
			<span>© <?php echo esc_html( bz_fa_digits( date_i18n( 'Y' ) ) ); ?> <?php bloginfo( 'name' ); ?> — تمامی حقوق محفوظ است.</span>
			<span>طراحی‌شده با ❤ برای فروشگاه شما</span>
		</div>
	</div>
</footer>

<button class="bz-to-top" id="bz-to-top" aria-label="بازگشت به بالا"><?php echo bz_icon( 'chevron', 20 ); ?></button>

<?php wp_footer(); ?>
</body>
</html>
