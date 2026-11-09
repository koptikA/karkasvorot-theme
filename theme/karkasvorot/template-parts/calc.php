<?php
/**
 * Size calculator: gate type, width, height, live SVG scheme. $args: type (slide|swing|wicket|canopy), heading (tag).
 * Canopy: A = width, B = length, the scheme is a plan view.
 * site.js redraws it and its button fills the order form (#kv-f-product, #kv-f-comment). Without JS the drawing
 * below (kvCalcGeom() output for the default sizes) stays and the button is a plain link to #zayavka.
 */
$kv_type  = isset( $args['type'] ) ? $args['type'] : 'slide';
$kv_tag   = isset( $args['heading'] ) ? $args['heading'] : 'h2';
$kv_prod  = kv_lead_products();
$kv_gate  = '1 7 4 1 2.5 1.8';
$kv_types = array( // type => [tab, form product, slider limits «wmin wmax wdef hmin hmax hdef», static drawing]
	'slide'  => array( 'Откатные', $kv_prod[0], $kv_gate, 'M70 91.7H483.8V250H70ZM483.8 91.7L670 155V250H483.8', 'M70 250L276.9 91.7L483.8 250M276.9 91.7V250', 77.7, 172.3, 485.8, 483.8, 276.9, 91.7, 170.9 ),
	'swing'  => array( 'Распашные', $kv_prod[1], $kv_gate, 'M70 66.4H307V250H70ZM313 66.4H550V250H313Z', 'M70 158.2H307M313 158.2H550', 52.4, 197.6, 552, 550, 310, 66.4, 158.2 ),
	'wicket' => array( 'Калитка', $kv_prod[2], '1 1.5 1 1 2.5 1.8', 'M70 20.5H220V250H70Z', 'M70 135.3H220', 6.5, 243.5, 222, 220, 145, 20.5, 135.3 ),
	'canopy' => array( 'Навес', $kv_prod[3], '2 8 4 3 10 6', 'M70 25H220V250H70Z', 'M70 62.5H220M70 100H220M70 137.5H220M70 175H220M70 212.5H220M64 19h12v12h-12ZM214 19h12v12h-12ZM64 131.5h12v12h-12ZM214 131.5h12v12h-12ZM64 244h12v12h-12ZM214 244h12v12h-12Z', 25, 0, 220, 220, 145, 25, 137.5 ),
);
$kv_t    = $kv_types[ $kv_type ];
$kv_lim  = explode( ' ', $kv_t[2] );
$kv_w    = number_format( (float) $kv_lim[2], 1, ',', '' );
$kv_h    = number_format( (float) $kv_lim[5], 1, ',', '' );
$kv_cn   = 'canopy' === $kv_type;
$kv_what = $kv_cn ? 'Навес' : ( 'wicket' === $kv_type ? 'Калитка' : 'Ворота' );
list( , , , $kv_fr, $kv_br, $kv_py, $kv_ph, $kv_p2, $kv_e, $kv_ta, $kv_y, $kv_tb ) = $kv_t;
// Texts that differ for the canopy: site.js swaps data-gate / data-canopy.
$kv_alt = function ( $gate, $canopy ) use ( $kv_cn ) {
	return 'data-gate="' . esc_attr( $gate ) . '" data-canopy="' . esc_attr( $canopy ) . '">' . esc_html( $kv_cn ? $canopy : $gate );
};
?>
<section class="kv-band kv-band--grid" id="raschet" aria-labelledby="kv-calc-title">
	<div class="kv-wrap kv-calc">
		<div class="kv-calc__copy">
			<p class="kv-tag">Расчёт под ваш проём</p>
			<<?php echo tag_escape( $kv_tag ); ?> class="kv-h2" id="kv-calc-title"><span class="kv-calc__what"><?php echo esc_html( $kv_what ); ?></span><br>шириной <span class="kv-calc__w"><?php echo esc_html( $kv_w ); ?></span>&nbsp;м?<br>Сварим.</<?php echo tag_escape( $kv_tag ); ?>>
			<p class="kv-lead">Выберите тип и двигайте ползунки — размеры попадут в заявку, мастер перезвонит и посчитает.</p>
			<ul class="kv-calc__legend">
				<li><b>A</b> — <span <?php echo $kv_alt( 'ширина проёма между столбами', 'ширина навеса' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?></span></li>
				<li><b>B</b> — <span <?php echo $kv_alt( 'высота ворот', 'длина навеса' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></li>
				<li>Не знаете точно — мастер подскажет по телефону</li>
			</ul>
		</div>
		<div class="kv-calc__stage">
			<div class="kv-calc__tabs" role="group" aria-label="Тип ворот">
				<?php foreach ( $kv_types as $kv_key => $kv_item ) : ?>
					<button type="button" data-type="<?php echo esc_attr( $kv_key ); ?>" data-product="<?php echo esc_attr( $kv_item[1] ); ?>" data-limits="<?php echo esc_attr( $kv_item[2] ); ?>" aria-pressed="<?php echo $kv_key === $kv_type ? 'true' : 'false'; ?>"><?php echo esc_html( $kv_item[0] ); ?></button>
				<?php endforeach; ?>
			</div>
			<svg class="kv-calc__svg<?php echo $kv_cn ? ' kv-calc--canopy' : ''; ?>" viewBox="0 0 760 300" role="img" aria-label="Схема каркаса с размерами A и B">
				<line class="kv-calc__ground" x1="0" y1="250" x2="760" y2="250"/>
				<rect class="kv-calc__post kv-calc__p1" x="54" y="<?php echo esc_attr( $kv_py ); ?>" width="14" height="<?php echo esc_attr( $kv_ph ); ?>"/>
				<rect class="kv-calc__post kv-calc__p2" x="<?php echo esc_attr( $kv_p2 ); ?>" y="<?php echo esc_attr( $kv_py ); ?>" width="14" height="<?php echo esc_attr( $kv_ph ); ?>"/>
				<path class="kv-calc__fr" d="<?php echo esc_attr( $kv_fr ); ?>"/>
				<path class="kv-calc__br" d="<?php echo esc_attr( $kv_br ); ?>"/>
				<path class="kv-calc__dim kv-calc__dA" d="M70 262V280M<?php echo esc_attr( $kv_e ); ?> 262V280M70 271H<?php echo esc_attr( $kv_e ); ?>"/>
				<text class="kv-calc__dt kv-calc__tA" x="<?php echo esc_attr( $kv_ta ); ?>" y="296" text-anchor="middle">A = <?php echo esc_html( $kv_w ); ?> м</text>
				<path class="kv-calc__dim kv-calc__dB" d="M36 <?php echo esc_attr( $kv_y ); ?>H46M36 250H46M41 <?php echo esc_attr( $kv_y ); ?>V250"/>
				<text class="kv-calc__dt kv-calc__tB" x="28" y="<?php echo esc_attr( $kv_tb ); ?>" text-anchor="middle" transform="rotate(-90 28 <?php echo esc_attr( $kv_tb ); ?>)">B = <?php echo esc_html( $kv_h ); ?> м</text>
			</svg>
			<div class="kv-calc__rows">
				<label for="kv-calc-w"><span <?php echo $kv_alt( 'A — ширина проёма', 'A — ширина навеса' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><output for="kv-calc-w"><?php echo esc_html( $kv_w ); ?> м</output></label>
				<input id="kv-calc-w" type="range" min="<?php echo esc_attr( $kv_lim[0] ); ?>" max="<?php echo esc_attr( $kv_lim[1] ); ?>" step="0.1" value="<?php echo esc_attr( $kv_lim[2] ); ?>">
				<label for="kv-calc-h"><span <?php echo $kv_alt( 'B — высота', 'B — длина навеса' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><output for="kv-calc-h"><?php echo esc_html( $kv_h ); ?> м</output></label>
				<input id="kv-calc-h" type="range" min="<?php echo esc_attr( $kv_lim[3] ); ?>" max="<?php echo esc_attr( $kv_lim[4] ); ?>" step="0.1" value="<?php echo esc_attr( $kv_lim[5] ); ?>">
			</div>
			<a class="kv-btn kv-calc__go" href="#zayavka">Рассчитать <?php echo esc_html( $kv_cn ? 'навес' : ( 'wicket' === $kv_type ? 'калитку' : 'ворота' ) ); ?> <?php echo esc_html( $kv_w . ' × ' . $kv_h ); ?> м</a>
		</div>
	</div>
</section>
