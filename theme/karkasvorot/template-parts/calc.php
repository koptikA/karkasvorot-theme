<?php
/**
 * Size calculator: gate type, width, height, live SVG scheme. $args: type (slide|swing|wicket), heading (tag).
 * site.js redraws it and its button fills the order form (#kv-f-product, #kv-f-comment). Without JS the drawing
 * below (kvCalcGeom() output for the default sizes) stays and the button is a plain link to #zayavka.
 */
$kv_type  = isset( $args['type'] ) ? $args['type'] : 'slide';
$kv_tag   = isset( $args['heading'] ) ? $args['heading'] : 'h2';
$kv_prod  = kv_lead_products();
$kv_types = array( // type => [tab, form product, max width, default width, static drawing]
	'slide'  => array( 'Откатные', $kv_prod[0], 7, 4, 'M70 91.7H483.8V250H70ZM483.8 91.7L670 155V250H483.8', 'M70 250L276.9 91.7L483.8 250M276.9 91.7V250', 77.7, 172.3, 485.8, 483.8, 276.9, 91.7, 170.9 ),
	'swing'  => array( 'Распашные', $kv_prod[1], 7, 4, 'M70 66.4H307V250H70ZM313 66.4H550V250H313Z', 'M70 158.2H307M313 158.2H550', 52.4, 197.6, 552, 550, 310, 66.4, 158.2 ),
	'wicket' => array( 'Калитка', $kv_prod[2], 1.5, 1, 'M70 20.5H220V250H70Z', 'M70 135.3H220', 6.5, 243.5, 222, 220, 145, 20.5, 135.3 ),
);
$kv_t    = $kv_types[ $kv_type ];
$kv_w    = number_format( $kv_t[3], 1, ',', '' );
$kv_what = 'wicket' === $kv_type ? 'Калитка' : 'Ворота';
list( , , , , $kv_fr, $kv_br, $kv_py, $kv_ph, $kv_p2, $kv_e, $kv_ta, $kv_y, $kv_tb ) = $kv_t;
?>
<section class="kv-band kv-band--grid" id="raschet" aria-labelledby="kv-calc-title">
	<div class="kv-wrap kv-calc">
		<div class="kv-calc__copy">
			<p class="kv-tag">Расчёт под ваш проём</p>
			<<?php echo tag_escape( $kv_tag ); ?> class="kv-h2" id="kv-calc-title"><span class="kv-calc__what"><?php echo esc_html( $kv_what ); ?></span><br>шириной <span class="kv-calc__w"><?php echo esc_html( $kv_w ); ?></span>&nbsp;м?<br>Сварим.</<?php echo tag_escape( $kv_tag ); ?>>
			<p class="kv-lead">Выберите тип и двигайте ползунки — размеры попадут в заявку, мастер перезвонит и посчитает.</p>
			<ul class="kv-calc__legend">
				<li><b>A</b> — ширина проёма между столбами</li>
				<li><b>B</b> — высота ворот</li>
				<li>Не знаете точно — мастер подскажет по телефону</li>
			</ul>
		</div>
		<div class="kv-calc__stage">
			<div class="kv-calc__tabs" role="group" aria-label="Тип ворот">
				<?php foreach ( $kv_types as $kv_key => $kv_item ) : ?>
					<button type="button" data-type="<?php echo esc_attr( $kv_key ); ?>" data-product="<?php echo esc_attr( $kv_item[1] ); ?>" data-max="<?php echo esc_attr( $kv_item[2] ); ?>" aria-pressed="<?php echo $kv_key === $kv_type ? 'true' : 'false'; ?>"><?php echo esc_html( $kv_item[0] ); ?></button>
				<?php endforeach; ?>
			</div>
			<svg class="kv-calc__svg" viewBox="0 0 760 300" role="img" aria-label="Схема каркаса с размерами A и B">
				<line class="kv-calc__ground" x1="0" y1="250" x2="760" y2="250"/>
				<rect class="kv-calc__post kv-calc__p1" x="54" y="<?php echo esc_attr( $kv_py ); ?>" width="14" height="<?php echo esc_attr( $kv_ph ); ?>"/>
				<rect class="kv-calc__post kv-calc__p2" x="<?php echo esc_attr( $kv_p2 ); ?>" y="<?php echo esc_attr( $kv_py ); ?>" width="14" height="<?php echo esc_attr( $kv_ph ); ?>"/>
				<path class="kv-calc__fr" d="<?php echo esc_attr( $kv_fr ); ?>"/>
				<path class="kv-calc__br" d="<?php echo esc_attr( $kv_br ); ?>"/>
				<path class="kv-calc__dim kv-calc__dA" d="M70 262V280M<?php echo esc_attr( $kv_e ); ?> 262V280M70 271H<?php echo esc_attr( $kv_e ); ?>"/>
				<text class="kv-calc__dt kv-calc__tA" x="<?php echo esc_attr( $kv_ta ); ?>" y="296" text-anchor="middle">A = <?php echo esc_html( $kv_w ); ?> м</text>
				<path class="kv-calc__dim kv-calc__dB" d="M36 <?php echo esc_attr( $kv_y ); ?>H46M36 250H46M41 <?php echo esc_attr( $kv_y ); ?>V250"/>
				<text class="kv-calc__dt kv-calc__tB" x="28" y="<?php echo esc_attr( $kv_tb ); ?>" text-anchor="middle" transform="rotate(-90 28 <?php echo esc_attr( $kv_tb ); ?>)">B = 1,8 м</text>
			</svg>
			<div class="kv-calc__rows">
				<label for="kv-calc-w"><span>A — ширина проёма</span><output for="kv-calc-w"><?php echo esc_html( $kv_w ); ?> м</output></label>
				<input id="kv-calc-w" type="range" min="1" max="<?php echo esc_attr( $kv_t[2] ); ?>" step="0.1" value="<?php echo esc_attr( $kv_t[3] ); ?>">
				<label for="kv-calc-h"><span>B — высота</span><output for="kv-calc-h">1,8 м</output></label>
				<input id="kv-calc-h" type="range" min="1" max="2.5" step="0.1" value="1.8">
			</div>
			<a class="kv-btn kv-calc__go" href="#zayavka">Рассчитать <?php echo 'wicket' === $kv_type ? 'калитку' : 'ворота'; ?> <?php echo esc_html( $kv_w ); ?> × 1,8 м</a>
		</div>
	</div>
</section>
