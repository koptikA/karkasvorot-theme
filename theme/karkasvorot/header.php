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
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'kv-menu',
				'depth'          => 2,
				'fallback_cb'    => 'kv_menu_fallback',
			) );
			?>
		</nav>
		<div class="kv-header__contacts">
			<?php
			echo kv_contact_link( 'phone', 'kv-header__phone' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
			echo kv_contact_link( 'viber', 'kv-header__msg kv-header__msg--viber' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo kv_contact_link( 'telegram', 'kv-header__msg kv-header__msg--telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
			<span class="kv-header__region">Минск и Минская область</span>
		</div>
	</div>
</header>
<main id="content" class="kv-main" tabindex="-1">
