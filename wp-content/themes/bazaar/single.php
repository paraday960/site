<?php
/**
 * قالب نوشته تکی
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<div class="container bz-single">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'bz-single-article' ); ?>>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-meta">
				✍ <?php the_author(); ?>
				&nbsp;|&nbsp; 📅 <?php echo esc_html( bz_post_date() ); ?>
				&nbsp;|&nbsp; ⏱ <?php echo esc_html( bz_fa_digits( get_the_time( 'H:i' ) ) ); ?>
			</div>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="bz-single-thumb"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>
			<div class="entry-content">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<nav class="bz-page-links">', 'after' => '</nav>' ) );
				?>
			</div>
		</article>

		<?php
		if ( comments_open() || get_comments_number() ) {
			echo '<div class="bz-comments">';
			comments_template();
			echo '</div>';
		}
		?>

		<nav class="post-navigation">
			<?php the_post_navigation( array(
				'prev_text' => '<span>‹ نوشته قبلی</span>',
				'next_text' => '<span>نوشته بعدی ›</span>',
			) ); ?>
		</nav>
	<?php endwhile; ?>
</div>
<?php get_footer(); ?>
