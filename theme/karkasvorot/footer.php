</main>
<footer class="kv-footer">
	<div class="kv-container kv-footer__inner">
		<div class="kv-footer__col">
			<p class="kv-footer__logo">KARKASVOROT.BY</p>
			<p class="kv-footer__region">Минск и Минская область, доставка каркасов по Беларуси</p>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer',
				'container'      => 'nav',
				'container_aria_label' => 'Меню в подвале',
				'menu_class'     => 'kv-footer__menu',
				'depth'          => 1,
				'fallback_cb'    => false,
			) );
			?>
		</div>
		<div class="kv-footer__col">
			<h2 class="kv-footer__title">Контакты</h2>
			<ul class="kv-footer__list">
				<?php
				foreach ( array( 'phone', 'viber', 'telegram' ) as $kv_type ) {
					$kv_link = kv_contact_link( $kv_type, 'kv-footer__link' );
					if ( '' !== $kv_link ) {
						echo '<li class="kv-footer__item">' . $kv_link . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
					}
				}
				kv_footer_field( 'email', 'E-mail' );
				kv_footer_field( 'hours', 'Часы работы' );
				?>
			</ul>
		</div>
		<div class="kv-footer__col">
			<h2 class="kv-footer__title">Реквизиты</h2>
			<ul class="kv-footer__list">
				<?php
				kv_footer_field( 'org_name', 'Наименование' );
				kv_footer_field( 'unp', 'УНП' );
				kv_footer_field( 'address', 'Адрес' );
				kv_footer_field( 'registration', 'Регистрация' );
				?>
			</ul>
		</div>
	</div>
	<div class="kv-container kv-footer__bottom">
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> KARKASVOROT.BY</span>
		<a class="kv-footer__policy" href="<?php echo esc_url( home_url( '/politika-konfidencialnosti/' ) ); ?>">Политика обработки персональных данных</a>
	</div>
</footer>
<nav class="kv-mobilebar" aria-label="Быстрая связь">
	<?php
	echo kv_contact_link( 'phone', 'kv-mobilebar__link kv-mobilebar__link--phone', 'Позвонить' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
	echo kv_contact_link( 'viber', 'kv-mobilebar__link kv-mobilebar__link--viber' ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo kv_contact_link( 'telegram', 'kv-mobilebar__link kv-mobilebar__link--telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput
	?>
	<a class="kv-mobilebar__link kv-mobilebar__link--order" href="#zayavka">Заявка</a>
</nav>
<?php wp_footer(); ?>
</body>
</html>
