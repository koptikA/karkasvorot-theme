<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-metrika="<?php echo esc_attr( kv_opt( 'metrika_id' ) ); ?>">
<?php wp_body_open(); ?>
<a class="kv-skip" href="#content">Перейти к содержимому</a>
<header class="kv-header">
	<div class="kv-container kv-header__inner">
		<a class="kv-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">KARKASVOROT.BY</a>
		<button class="kv-header__burger" type="button" aria-expanded="false" aria-controls="kv-nav">
			<span class="kv-header__burger-icon" aria-hidden="true"></span><span class="kv-visually-hidden">Меню</span>
		</button>
		<nav id="kv-nav" class="kv-header__nav" aria-label="Главное меню">
            <ul class="kv-menu kv-menu--compact">
                <li class="kv-products">
                    <details class="kv-products__dropdown">
                        <summary>Продукция <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg></summary>
                        <ul class="kv-products__list">
                            <?php foreach ( kv_service_pages() as $kv_page ) : ?>
                                <li><a href="<?php echo esc_url( get_permalink( $kv_page ) ); ?>"<?php if ( is_page( $kv_page->ID ) ) { echo ' aria-current="page"'; } ?>><?php echo esc_html( get_the_title( $kv_page ) ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                </li>
                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Наши работы</a></li>
                <li><a href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>">Контакты</a></li>
            </ul>
		</nav>
        <div class="kv-header__contacts">
            <?php get_template_part( 'template-parts/social-links' ); ?>
            <?php echo kv_contact_link( 'phone', 'kv-header__phone' ); ?>
        </div>
	</div>
</header>
<main id="content" class="kv-main" tabindex="-1">
