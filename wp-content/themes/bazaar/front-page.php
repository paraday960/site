<?php
/**
 * صفحه نخست فروشگاهی قالب بازار
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
$has_wc = class_exists( 'WooCommerce' );
?>

<!-- ===================== قهرمان (Hero) ===================== -->
<section class="bz-hero">
	<div class="container bz-hero-inner">
		<div>
			<span class="bz-hero-badge">✦ فروشگاه اینترنتی بازار — خرید راحت و مطمئن</span>
			<h1><?php echo wp_kses_post( bz_get_option( 'hero_title' ) ); ?></h1>
			<p><?php echo esc_html( bz_get_option( 'hero_subtitle' ) ); ?></p>
			<div class="bz-hero-actions">
				<?php
				$btn_url = bz_get_option( 'hero_btn_url' );
				if ( ! $btn_url && $has_wc ) { $btn_url = wc_get_page_permalink( 'shop' ); }
				?>
				<a class="button bz-btn-accent" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( bz_get_option( 'hero_btn' ) ); ?></a>
				<?php if ( $has_wc ) : ?>
					<a class="button bz-btn-ghost" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">ثبت‌نام / ورود</a>
				<?php endif; ?>
			</div>
			<div class="bz-hero-stats">
				<div><b><?php echo bz_fa_digits( '+۱۰,۰۰۰' ); ?></b><span>محصول متنوع</span></div>
				<div><b><?php echo bz_fa_digits( '+۵۰,۰۰۰' ); ?></b><span>مشتری راضی</span></div>
				<div><b>۲۴/۷</b><span>پشتیبانی واقعی</span></div>
			</div>
		</div>
		<div class="bz-hero-visual" aria-hidden="true">
			<span class="bz-hero-chip">تا ۲۰٪ تخفیف</span>
			<div class="bz-hero-card bz-hero-card-main">
				<?php
				if ( $has_wc ) {
					$featured = wc_get_products( array( 'limit' => 1, 'orderby' => 'rand', 'return' => 'objects' ) );
					if ( $featured ) {
						$p       = $featured[0];
						$gallery = $p->get_image_id();
						if ( $gallery ) {
							echo wp_get_attachment_image( $gallery, 'woocommerce_thumbnail', false, array( 'alt' => '' ) );
						}
						echo '<div class="bz-hc-body"><div class="bz-hc-title">' . esc_html( $p->get_name() ) . '</div><div class="bz-hc-price">';
						if ( $p->is_on_sale() && $p->get_regular_price() ) { echo '<del>' . wp_kses_post( wc_price( $p->get_regular_price() ) ) . '</del>'; }
						echo '<ins>' . wp_kses_post( $p->get_price() ? wc_price( $p->get_price() ) : '' ) . '</ins></div></div>';
					}
				}
				?>
			</div>
			<div class="bz-hero-card bz-hero-card-float">
				<span class="bz-dot" style="background:var(--bz-primary-soft);color:var(--bz-primary)"><?php echo bz_icon( 'truck', 20 ); ?></span>
				ارسال سریع به سراسر کشور
			</div>
		</div>
	</div>
</section>

<!-- ===================== مزیت‌ها ===================== -->
<div class="bz-features">
	<div class="container">
		<div class="bz-features-grid">
			<div class="bz-feature">
				<span class="bz-feature-icon"><?php echo bz_icon( 'truck', 26 ); ?></span>
				<div><b>ارسال سریع</b><span>تحویل اکسپرس در تهران</span></div>
			</div>
			<div class="bz-feature">
				<span class="bz-feature-icon"><?php echo bz_icon( 'shield', 26 ); ?></span>
				<div><b>ضمانت اصالت</b><span>کالای اورجینال و اصل</span></div>
			</div>
			<div class="bz-feature">
				<span class="bz-feature-icon"><?php echo bz_icon( 'return', 26 ); ?></span>
				<div><b>۷ روز بازگشت</b><span>بدون قید و شرط</span></div>
			</div>
			<div class="bz-feature">
				<span class="bz-feature-icon"><?php echo bz_icon( 'support', 26 ); ?></span>
				<div><b>پشتیبانی ۲۴/۷</b><span>همیشه پاسخگوی شما</span></div>
			</div>
		</div>
	</div>
</div>

<?php if ( $has_wc ) : ?>

	<!-- ===================== دسته‌بندی‌ها ===================== -->
	<section class="bz-section">
		<div class="container">
			<div class="bz-section-head">
				<h2 class="bz-section-title">دسته‌بندی‌های محبوب</h2>
				<a class="bz-section-more" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">مشاهده همه <span>‹</span></a>
			</div>
			<div class="bz-categories-grid">
				<?php
				$bz_cats = array(
					'mobile'   => 'موبایل و تبلت',
					'laptop'   => 'لپ‌تاپ و کامپیوتر',
					'shirt'    => 'پوشاک',
					'home'     => 'لوازم خانگی',
					'download' => 'محصولات دانلودی',
					'beauty'   => 'زیبایی و سلامت',
				);
				$bz_terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => 0, 'number' => 12 ) );
				$bz_icons = array( 'mobile', 'laptop', 'shirt', 'home', 'download', 'beauty' );
				$bz_i     = 0;
				if ( ! is_wp_error( $bz_terms ) ) {
					foreach ( $bz_terms as $bz_term ) {
						if ( 'uncategorized' === $bz_term->slug ) { continue; }
						$bz_icon_name = $bz_icons[ $bz_i % count( $bz_icons ) ];
						$bz_i++;
						?>
						<a class="bz-category" href="<?php echo esc_url( get_term_link( $bz_term ) ); ?>">
							<?php echo bz_icon( $bz_icon_name, 44 ); ?>
							<b><?php echo esc_html( $bz_term->name ); ?></b>
							<span><?php echo esc_html( bz_fa_digits( $bz_term->count ) ); ?> محصول</span>
						</a>
						<?php
					}
				}
				?>
			</div>
		</div>
	</section>

	<!-- ===================== محصولات ویژه ===================== -->
	<section class="bz-section" style="padding-top:0">
		<div class="container">
			<div class="bz-section-head">
				<h2 class="bz-section-title">محصولات ویژه</h2>
				<a class="bz-section-more" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">مشاهده همه <span>‹</span></a>
			</div>
			<?php echo do_shortcode( '[products limit="8" columns="4" visibility="featured"]' ); ?>
		</div>
	</section>

	<!-- ===================== بنر تخفیف ===================== -->
	<section class="bz-section" style="padding-top:0">
		<div class="container">
			<div class="bz-promo">
				<div>
					<h3>🔥 تخفیف‌های ویژه این هفته</h3>
					<p>تا ۳۰٪ تخفیف روی کالاهای منتخب — فرصت محدود!</p>
				</div>
				<a class="button" href="<?php echo esc_url( add_query_arg( 'on_sale', '1', wc_get_page_permalink( 'shop' ) ) ); ?>">مشاهده تخفیف‌ها</a>
			</div>
		</div>
	</section>

	<!-- ===================== پرفروش‌ترین‌ها ===================== -->
	<section class="bz-section" style="padding-top:0">
		<div class="container">
			<div class="bz-section-head">
				<h2 class="bz-section-title">پرفروش‌ترین‌ها</h2>
			</div>
			<?php echo do_shortcode( '[best_selling_products limit="4" columns="4"]' ); ?>
		</div>
	</section>

	<!-- ===================== جدیدترین‌ها ===================== -->
	<section class="bz-section" style="padding-top:0">
		<div class="container">
			<div class="bz-section-head">
				<h2 class="bz-section-title">جدیدترین محصولات</h2>
				<a class="bz-section-more" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">مشاهده همه <span>‹</span></a>
			</div>
			<?php echo do_shortcode( '[recent_products limit="8" columns="4"]' ); ?>
		</div>
	</section>

<?php endif; ?>

<!-- ===================== وبلاگ ===================== -->
<?php
$bz_posts = new WP_Query( array( 'posts_per_page' => 3, 'ignore_sticky_posts' => true ) );
if ( $bz_posts->have_posts() ) :
	?>
	<section class="bz-section" style="padding-top:0">
		<div class="container">
			<div class="bz-section-head">
				<h2 class="bz-section-title">از وبلاگ بازار</h2>
				<a class="bz-section-more" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">مشاهده همه <span>‹</span></a>
			</div>
			<div class="bz-posts-grid">
				<?php
				while ( $bz_posts->have_posts() ) :
					$bz_posts->the_post();
					?>
					<article <?php post_class( 'bz-post-card' ); ?>>
						<a class="bz-post-thumb" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?>
						</a>
						<div class="bz-post-body">
							<div class="bz-post-meta">
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">📅 <?php echo esc_html( bz_post_date() ); ?></time>
							</div>
							<h2><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
							<a class="bz-post-more" href="<?php the_permalink(); ?>">ادامه مطلب <span>‹</span></a>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ===================== خبرنامه ===================== -->
<section class="bz-section" style="padding-top:0">
	<div class="container">
		<div class="bz-newsletter">
			<h3>📮 از تخفیف‌ها جا نمون!</h3>
			<p>ایمیلت رو وارد کن تا جدیدترین محصولات و کدهای تخفیف رو زودتر از همه دریافت کنی.</p>
			<form onsubmit="this.reset();return false">
				<input type="email" placeholder="ایمیل شما…" required>
				<button type="submit">عضویت</button>
			</form>
		</div>
	</div>
</section>

<?php get_footer(); ?>
