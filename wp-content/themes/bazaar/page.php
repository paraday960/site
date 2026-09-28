<?php
/**
 * قالب برگه
 *
 * @package Bazaar
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<div class="bz-page-title-wrap">
	<div class="container">
		<h1 class="bz-page-title"><?php the_title(); ?></h1>
	</div>
</div>
<div class="container bz-archive">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'bz-single-article bz-page-content' ); ?>>
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
	<?php endwhile; ?>
</div>
<?php get_footer(); ?>
