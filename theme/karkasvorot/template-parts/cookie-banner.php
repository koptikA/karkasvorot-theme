<?php
/**
 * Cookie consent banner. Hidden until site.js finds no saved choice in localStorage kv_consent.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="kv-cookie" role="region" aria-label="Согласие на cookie" hidden>
	<div class="kv-container kv-cookie__inner">
		<p class="kv-cookie__text">Мы используем cookie и Яндекс.Метрику, чтобы понимать, как улучшить сайт. Метрика включится только с вашего согласия. Подробнее — в <a href="<?php echo esc_url( home_url( '/politika-konfidencialnosti/' ) ); ?>">политике обработки персональных данных</a>.</p>
		<div class="kv-cookie__actions">
			<button class="kv-cookie__btn kv-cookie__btn--accept" type="button" data-consent="yes">Принять</button>
			<button class="kv-cookie__btn kv-cookie__btn--decline" type="button" data-consent="no">Отклонить</button>
		</div>
	</div>
</div>
