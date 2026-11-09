</main>
<footer class="kv-footer">
	<div class="kv-wrap">
		<div class="kv-stamp">
			<div class="kv-stamp__cell">
				<p class="kv-stamp__label">Изготовитель</p>
				<p class="kv-stamp__big">KARKASVOROT.BY</p>
				<?php if ( current_user_can( 'manage_options' ) || '' !== kv_opt( 'org_name' ) . kv_opt( 'unp' ) . kv_opt( 'address' ) ) : ?>
					<ul class="kv-footer__list">
						<?php
						kv_footer_field( 'org_name', 'Наименование' );
						kv_footer_field( 'unp', 'УНП' );
						kv_footer_field( 'address', 'Адрес' );
						?>
					</ul>
				<?php endif; ?>
			</div>
			<nav class="kv-stamp__cell" aria-label="Разделы сайта">
				<p class="kv-stamp__label">Изделия</p>
				<ul class="kv-footer__list">
					<?php foreach ( kv_service_pages() as $kv_page ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $kv_page ) ); ?>"><?php echo esc_html( get_the_title( $kv_page ) ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Наши работы</a></li>
					<li><a href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>">Контакты</a></li>
				</ul>
			</nav>
			<div class="kv-stamp__cell">
				<p class="kv-stamp__label">География</p>
				<p>Изготовление — Минск<br>Монтаж — Минск и Минская область (в т.ч. Червенский район)<br>Доставка каркасов — вся Беларусь</p>
			</div>
			<div class="kv-stamp__cell">
				<p class="kv-stamp__label">Связь</p>
				<ul class="kv-footer__list">
					<?php
					$kv_link = kv_contact_link( 'phone', 'kv-footer__phone' );
					if ( '' !== $kv_link ) {
						echo '<li>' . $kv_link . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
					}
					kv_footer_field( 'email', 'E-mail' );
					kv_footer_field( 'hours', 'Часы работы' );
					?>
				</ul>
				<?php get_template_part( 'template-parts/social-links' ); ?>
			</div>
		</div>
		<div class="kv-footer__bottom">
			<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> KARKASVOROT.BY</span>
			<a href="<?php echo esc_url( home_url( '/politika-konfidencialnosti/' ) ); ?>">Политика обработки персональных данных</a>
			<?php if ( '' !== kv_opt( 'metrika_id' ) ) : ?>
				<button class="kv-footer__cookie" type="button" data-consent-open>Настройки cookie</button>
			<?php endif; ?>
		</div>
	</div>
</footer>
<nav class="kv-mobilebar" aria-label="Быстрая связь">
	<?php
	if ( kv_phone_href() ) {
		echo '<a class="kv-mobilebar__link kv-mobilebar__link--phone" href="' . esc_url( kv_phone_href(), array( 'tel' ) ) . '" aria-label="Позвонить" title="Позвонить" data-goal="phone_click">' . file_get_contents( KV_DIR . '/assets/icons/phone.svg' ) . '</a>'; // Trusted bundled SVG; escaped phone URL.
	}
	get_template_part( 'template-parts/social-links' );
	?>
	<a class="kv-mobilebar__link kv-mobilebar__link--order" href="#zayavka">Заявка</a>
</nav>
<?php wp_footer(); ?>
</body>
</html>
