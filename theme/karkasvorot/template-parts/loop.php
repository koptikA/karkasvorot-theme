<?php
/**
 * Main-query list: card grid, pagination, «пусто». Used by index.php, archive.php, search.php.
 */
if ( have_posts() ) : ?>
	<div class="kv-grid">
		<?php
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/card' );
		}
		?>
	</div>
	<?php the_posts_pagination( array( 'class' => 'kv-pagination' ) ); ?>
<?php else : ?>
	<p>Здесь пока ничего нет.</p>
<?php endif; ?>
