<?php
/**
 * «Контакты» (slug kontakty): phone, Viber, Telegram, WhatsApp, work area, hours, requisites, order form.
 */
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'kv-container kv-section kv-contacts' ); ?>>
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1 class="kv-title"><?php the_title(); ?></h1>
		<div class="kv-content"><?php the_content(); ?></div>
		<div class="kv-contacts__grid">
			<section class="kv-contacts__block" aria-labelledby="kv-contacts-links">
				<h2 id="kv-contacts-links">Связаться</h2>
				<ul class="kv-contacts__list">
					<?php
					foreach ( array( 'phone' => 'Телефон', 'viber' => 'Viber', 'telegram' => 'Telegram', 'whatsapp' => 'WhatsApp' ) as $kv_type => $kv_label ) {
						$kv_link = kv_contact_link( $kv_type, 'kv-contacts__link', 'phone' === $kv_type ? '' : 'Написать в ' . $kv_label );
						if ( '' !== $kv_link ) {
							echo '<li class="kv-footer__item"><span class="kv-footer__label">' . esc_html( $kv_label ) . ':</span> ' . $kv_link . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
						}
					}
					kv_footer_field( 'email', 'E-mail' );
					kv_footer_field( 'hours', 'Часы работы' );
					?>
					<li class="kv-footer__item"><span class="kv-footer__label">Зона работы:</span> изготовление в Минске · монтаж в Минске и Минской области (в т.ч. Червенский район) · доставка каркасов по всей Беларуси</li>
				</ul>
			</section>
			<?php
			$kv_req = array( 'org_name' => 'Наименование', 'unp' => 'УНП', 'address' => 'Адрес', 'registration' => 'Регистрация' );
			if ( current_user_can( 'manage_options' ) || array_filter( array_map( 'kv_opt', array_keys( $kv_req ) ) ) ) :
				?>
			<section class="kv-contacts__block" aria-labelledby="kv-contacts-req">
				<h2 id="kv-contacts-req">Реквизиты</h2>
				<ul class="kv-contacts__list">
					<?php
					foreach ( $kv_req as $kv_key => $kv_label ) {
						kv_footer_field( $kv_key, $kv_label );
					}
					?>
				</ul>
			</section>
			<?php endif; ?>
		</div>
		<?php kv_order_form(); ?>
	</article>
	<?php
endwhile;
get_footer();
