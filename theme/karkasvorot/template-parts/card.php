<?php
/**
 * Card of a post in lists (archives, search, blog).
 */
?>
<article <?php post_class( 'kv-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="kv-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'kv-card', array( 'class' => 'kv-card__img', 'loading' => 'lazy' ) ); ?></a>
	<?php endif; ?>
	<h2 class="kv-card__title"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ? get_the_title() : 'Без названия' ); ?></a></h2>
	<?php if ( has_excerpt() ) : ?>
		<p class="kv-card__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
	<?php endif; ?>
</article>
