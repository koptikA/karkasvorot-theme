</main>
<footer class="kv-footer">
	<div class="kv-container kv-footer__inner">
		<div class="kv-footer__col">
			<p class="kv-footer__logo">KARKASVOROT.BY</p>
			<p class="kv-footer__region">Минск и Минская область, доставка каркасов по Беларуси</p>

		</div>
        <nav class="kv-footer__col kv-footer__navigation" aria-label="Разделы сайта">
            <h2 class="kv-footer__title">Продукция</h2>
            <ul class="kv-footer__list">
            <?php foreach ( kv_service_pages() as $kv_page ) : ?>
                <li><a href="<?php echo esc_url( get_permalink( $kv_page ) ); ?>"><?php echo esc_html( get_the_title( $kv_page ) ); ?></a></li>
            <?php endforeach; ?>
                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Наши работы</a></li>
                <li><a href="<?php echo esc_url( home_url( '/kontakty/' ) ); ?>">Контакты</a></li>
            </ul>
        </nav>
		<div class="kv-footer__col">
			<h2 class="kv-footer__title">Контакты</h2>
			<ul class="kv-footer__list">
				<?php
				foreach ( array( 'phone' ) as $kv_type ) {
					$kv_link = kv_contact_link( $kv_type, 'kv-footer__link' );
					if ( '' !== $kv_link ) {
						echo '<li class="kv-footer__item">' . $kv_link . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
					}
				}
				kv_footer_field( 'email', 'E-mail' );
				kv_footer_field( 'hours', 'Часы работы' );
				?>
			</ul>
			<?php get_template_part( 'template-parts/social-links' ); ?>
		</div>
		<?php if ( current_user_can( 'manage_options' ) || '' !== kv_opt( 'org_name' ) . kv_opt( 'unp' ) . kv_opt( 'address' ) . kv_opt( 'registration' ) ) : ?>
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
		<?php endif; ?>
	</div>
	<div class="kv-container kv-footer__bottom">
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> KARKASVOROT.BY</span>
		<a class="kv-footer__policy" href="<?php echo esc_url( home_url( '/politika-konfidencialnosti/' ) ); ?>">Политика обработки персональных данных</a>
		<?php if ( '' !== kv_opt( 'metrika_id' ) ) : ?>
			<button class="kv-footer__cookie" type="button" data-consent-open>Настройки cookie</button>
		<?php endif; ?>
	</div>
</footer>
<nav class="kv-mobilebar" aria-label="Быстрая связь">
	<?php
	echo kv_contact_link( 'phone', 'kv-mobilebar__link kv-mobilebar__link--phone', 'Позвонить' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
	get_template_part( 'template-parts/social-links' );
	?>
	<a class="kv-mobilebar__link kv-mobilebar__link--order" href="#zayavka">Заявка</a>
</nav>
<?php wp_footer(); ?>
</body>
</html>
