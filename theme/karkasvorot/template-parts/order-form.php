<?php
/**
 * Order form. Rendered once per page by kv_order_form(); $args: status ('', ok, err), values, errors.
 */

defined( 'ABSPATH' ) || exit;

$kv_v      = wp_parse_args( $args['values'], array( 'name' => '', 'phone' => '', 'product' => '', 'comment' => '', 'consent' => false ) );
$kv_e      = $args['errors'];
$kv_labels = array( 'phone' => 'Телефон', 'consent' => 'Согласие' );
$kv_back   = remove_query_arg( array( 'zayavka', 'kv_req' ), home_url( add_query_arg( array() ) ) );
$kv_attr   = function ( $field ) use ( $kv_e ) {
	return isset( $kv_e[ $field ] ) ? ' aria-invalid="true" aria-describedby="kv-f-' . $field . '-err"' : '';
};
$kv_err    = function ( $field ) use ( $kv_e ) {
	if ( isset( $kv_e[ $field ] ) ) {
		echo '<p class="kv-form__error" id="kv-f-' . esc_attr( $field ) . '-err">' . esc_html( $kv_e[ $field ] ) . '</p>';
	}
};
?>
<section class="kv-order" aria-labelledby="kv-order-title">
	<div class="kv-container">
		<form id="zayavka" class="kv-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<h2 class="kv-form__title" id="kv-order-title">Оставить заявку</h2>
			<p class="kv-form__lead">Перезвоним, уточним размеры и посчитаем стоимость.</p>

			<?php if ( 'ok' === $args['status'] ) : ?>
				<div class="kv-form__success" role="status" tabindex="-1" data-goal-auto="form_submit">Спасибо! Мы перезвоним в ближайшее время.</div>
			<?php elseif ( $kv_e ) : ?>
				<div class="kv-form__summary" role="alert" tabindex="-1">
					<p>Заявка не отправлена:</p>
					<ul>
						<?php foreach ( $kv_e as $kv_f => $kv_msg ) : ?>
							<li><?php echo isset( $kv_labels[ $kv_f ] ) ? '<a href="#kv-f-' . esc_attr( $kv_f ) . '">' . esc_html( $kv_msg ) . '</a>' : esc_html( $kv_msg ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="kv-form__grid">
				<p class="kv-form__field">
					<label class="kv-form__label" for="kv-f-name">Имя</label>
					<input class="kv-form__input" id="kv-f-name" type="text" name="kv_name" autocomplete="name" maxlength="100" value="<?php echo esc_attr( $kv_v['name'] ); ?>">
				</p>
				<div class="kv-form__field">
					<label class="kv-form__label" for="kv-f-phone">Телефон <span class="kv-form__req">(обязательно)</span></label>
					<input class="kv-form__input" id="kv-f-phone" type="tel" name="kv_phone" autocomplete="tel" inputmode="tel" placeholder="+375 29 123-45-67" maxlength="40" required value="<?php echo esc_attr( $kv_v['phone'] ); ?>"<?php echo $kv_attr( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup. ?>>
					<?php $kv_err( 'phone' ); ?>
				</div>
				<p class="kv-form__field">
					<label class="kv-form__label" for="kv-f-product">Изделие</label>
					<select class="kv-form__input" id="kv-f-product" name="kv_product">
						<option value="">Выберите изделие</option>
						<?php foreach ( kv_lead_products() as $kv_p ) : ?>
							<option value="<?php echo esc_attr( $kv_p ); ?>"<?php selected( $kv_v['product'], $kv_p ); ?>><?php echo esc_html( $kv_p ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="kv-form__field kv-form__field--wide">
					<label class="kv-form__label" for="kv-f-comment">Комментарий</label>
					<textarea class="kv-form__input" id="kv-f-comment" name="kv_comment" rows="3" maxlength="2000" placeholder="Размеры проёма, город, сроки"><?php echo esc_textarea( $kv_v['comment'] ); ?></textarea>
				</p>
			</div>

			<div class="kv-form__field kv-form__consent">
				<input class="kv-form__check" id="kv-f-consent" type="checkbox" name="kv_consent" value="1" required<?php checked( $kv_v['consent'] ); ?><?php echo $kv_attr( 'consent' ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup. ?>>
				<label for="kv-f-consent">Согласен на обработку персональных данных согласно <a href="<?php echo esc_url( home_url( '/politika-konfidencialnosti/' ) ); ?>">политике</a></label>
				<?php $kv_err( 'consent' ); ?>
			</div>

			<div class="kv-form__hp" aria-hidden="true">
				<label for="kv-f-hp">Не заполняйте это поле</label>
				<input id="kv-f-hp" type="text" name="kv_hp" value="" tabindex="-1" autocomplete="off">
			</div>
			<input type="hidden" name="action" value="kv_order">
			<input type="hidden" name="kv_ts" value="<?php echo esc_attr( time() ); ?>">
			<input type="hidden" name="kv_back" value="<?php echo esc_url( $kv_back ); ?>">
			<input type="hidden" name="kv_req" value="<?php echo esc_attr( wp_generate_uuid4() ); ?>">

			<button class="kv-form__submit" type="submit">Отправить заявку</button>
		</form>
	</div>
</section>
