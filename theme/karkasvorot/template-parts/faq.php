<?php
/**
 * Questions and answers from the page field _kv_faq. $args: page_id.
 */
$kv_faq = json_decode( (string) get_post_meta( $args['page_id'], '_kv_faq', true ), true );
if ( ! is_array( $kv_faq ) || ! $kv_faq ) {
	return;
}
?>
<section class="kv-faq" aria-labelledby="kv-faq-title">
	<h2 id="kv-faq-title">Вопросы и ответы</h2>
	<?php foreach ( $kv_faq as $kv_item ) : ?>
		<?php if ( isset( $kv_item['q'], $kv_item['a'] ) ) : ?>
			<details class="kv-faq__item">
				<summary class="kv-faq__q"><?php echo esc_html( $kv_item['q'] ); ?></summary>
				<div class="kv-faq__a"><?php echo wp_kses_post( wpautop( $kv_item['a'] ) ); ?></div>
			</details>
		<?php endif; ?>
	<?php endforeach; ?>
</section>
