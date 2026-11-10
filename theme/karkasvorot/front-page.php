<?php
/** Home: dark workshop hero, size calculator, products, orbit gallery of works, steps, order form. */
get_header();
$kv_hero  = kv_hero_image();
$kv_works = kv_works( 0, 7 );
$kv_items = array( 'Откатные ворота', 'Распашные ворота', 'Калитки', 'Навесы', 'Заборы', 'Каркасы мебели' );
?>
<section class="kv-hero" aria-labelledby="kv-hero-title">
	<img class="kv-hero__bg" src="<?php echo esc_url( $kv_hero['url'] ); ?>" width="<?php echo (int) $kv_hero['width']; ?>" height="<?php echo (int) $kv_hero['height']; ?>" alt="Тёмный забор с калиткой из металлоштакетника — наша работа" fetchpriority="high" decoding="async">
	<div class="kv-hero__in">
		<p class="kv-tag kv-hero__kicker">Мастерская металлоконструкций · Минск</p>
		<h1 class="kv-hero__title" id="kv-hero-title"><span>Варим</span><br>ворота<br><b>под проём</b></h1>
		<p class="kv-lead">Каркасы откатных и распашных ворот, калитки, навесы и мебель. Изготовление в Минске, монтаж в Минске и Минской области, доставка каркасов по всей Беларуси.</p>
		<div class="kv-hero__cta"><a class="kv-btn" href="#raschet">Указать размер проёма</a><a class="kv-btn kv-btn--ghost" href="#raboty">Смотреть работы</a></div>
	</div>
	<div class="kv-hero__sparks" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
	<div class="kv-hero__strip" aria-hidden="true"><div><?php for ( $kv_i = 0; $kv_i < 2; $kv_i++ ) { foreach ( $kv_items as $kv_item ) { echo '<span>' . esc_html( $kv_item ) . '</span><em>✕</em>'; } } ?></div></div>
</section>

<?php get_template_part( 'template-parts/calc' ); ?>

<section class="kv-band" id="izdeliya" aria-labelledby="kv-products-title">
	<div class="kv-wrap">
		<div class="kv-head"><div><p class="kv-tag">Изделия</p><h2 class="kv-h2" id="kv-products-title">Что варим</h2></div><p class="kv-tag kv-tag--muted">Стоимость рассчитывается индивидуально — по размеру и комплектации</p></div>
		<div class="kv-products">
			<?php
			$kv_labels = array( 'karkas-otkatnyh-vorot' => 'Откатные ворота', 'karkas-raspashnyh-vorot' => 'Распашные ворота', 'kalitki' => 'Калитки', 'navesy-dlya-avto' => 'Навесы для авто', 'prochie-izdeliya' => 'Мебель и другое' );
			foreach ( kv_service_pages() as $kv_n => $kv_page ) :
				$kv_ill   = (int) get_post_meta( $kv_page->ID, '_kv_illustration', true );
				$kv_label = isset( $kv_labels[ $kv_page->post_name ] ) ? $kv_labels[ $kv_page->post_name ] : get_the_title( $kv_page );
				$kv_spec  = array_slice( kv_service_price( $kv_page->ID )['includes'], 0, 3 );
				?>
				<a class="kv-product" href="<?php echo esc_url( get_permalink( $kv_page ) ); ?>">
					<span class="kv-product__no"><?php echo esc_html( sprintf( '%02d', $kv_n + 1 ) ); ?></span>
					<?php $kv_draw = kv_page_drawing( $kv_page, 'kv-product__img' ); echo '' !== $kv_draw ? $kv_draw : ( $kv_ill ? wp_get_attachment_image( $kv_ill, 'kv-card', false, array( 'class' => 'kv-product__img', 'loading' => 'lazy', 'alt' => 'Иллюстрация: ' . $kv_label ) ) : '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
					<h3><?php echo esc_html( $kv_label ); ?></h3>
					<?php if ( $kv_spec ) : ?>
						<span class="kv-product__spec"><?php echo implode( '<br>', array_map( function ( $line ) { return preg_replace( '/\d[\d,]*(?:×[\d,]+)+/u', '<b>$0</b>', esc_html( $line ) ); }, $kv_spec ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped per line. ?></span>
					<?php endif; ?>
					<span class="kv-product__go">Подробнее</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( $kv_works ) : ?>
<section class="kv-orbit" id="raboty" aria-labelledby="kv-works-title" style="--n:<?php echo count( $kv_works ); ?>">
	<div class="kv-orbit__pin">
		<div class="kv-orbit__band" aria-hidden="true"></div>
		<div class="kv-orbit__text">
			<p class="kv-tag">Сделано в нашей мастерской</p>
			<h2 class="kv-h2 kv-orbit__h" id="kv-works-title">Наши работы</h2>
			<div class="kv-orbit__titles" aria-hidden="true">
				<?php foreach ( $kv_works as $kv_i => $kv_work ) : $kv_words = explode( ' ', get_the_title( $kv_work ), 2 ); ?>
					<p class="kv-orbit__title<?php echo 0 === $kv_i ? ' is-on' : ''; ?>"><b><?php echo esc_html( $kv_words[0] ); ?></b><span><?php echo esc_html( isset( $kv_words[1] ) ? $kv_words[1] : '' ); ?></span></p>
				<?php endforeach; ?>
			</div>
			<div class="kv-orbit__card">
				<p class="kv-orbit__count"><b>01</b><small> / <?php echo esc_html( sprintf( '%02d', count( $kv_works ) ) ); ?></small></p>
				<p class="kv-orbit__cap"><?php echo esc_html( get_the_title( $kv_works[0] ) ); ?></p>
				<a class="kv-orbit__all" href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Все работы</a>
			</div>
		</div>
		<div class="kv-orbit__stage">
			<?php foreach ( $kv_works as $kv_work ) : $kv_img = get_post_thumbnail_id( $kv_work ); $kv_name = get_the_title( $kv_work ); ?>
				<a class="kv-orbit__item" href="<?php echo esc_url( get_permalink( $kv_work ) ); ?>" data-title="<?php echo esc_attr( $kv_name ); ?>">
					<?php echo $kv_img ? wp_get_attachment_image( $kv_img, 'kv-card', false, array( 'class' => 'kv-orbit__img', 'alt' => kv_image_alt( $kv_img, $kv_name ), 'loading' => 'lazy', 'sizes' => '(max-width: 900px) 72vw, 520px' ) ) : ''; ?>
					<span class="kv-orbit__name"><?php echo esc_html( $kv_name ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<p class="kv-orbit__hint" aria-hidden="true">Листайте вниз</p>
	</div>
</section>
<div class="kv-wrap kv-orbit__more"><a class="kv-btn" href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Смотреть все работы</a></div>
<?php endif; ?>

<section class="kv-band" aria-labelledby="kv-steps-title">
	<div class="kv-wrap">
		<div class="kv-head"><div><p class="kv-tag">Порядок работы</p><h2 class="kv-h2" id="kv-steps-title">От заявки до ворот</h2></div></div>
		<ol class="kv-flow">
			<li><span class="kv-flow__n" aria-hidden="true">01</span><h3>Заявка</h3><p>Оставьте телефон и размеры, если знаете.</p></li>
			<li><span class="kv-flow__n" aria-hidden="true">02</span><h3>Звонок мастера</h3><p>Согласуем вид, высоту и длину каркаса, посчитаем стоимость.</p></li>
			<li><span class="kv-flow__n" aria-hidden="true">03</span><h3>Изготовление</h3><p>Варим каркас в мастерской в Минске, грунтуем.</p></li>
			<li><span class="kv-flow__n" aria-hidden="true">04</span><h3>Монтаж и доставка</h3><p>Монтируем в Минске и Минской области, каркасы доставляем по всей Беларуси.</p></li>
		</ol>
	</div>
</section>
<section class="kv-band kv-band--grid" aria-labelledby="kv-crew-title">
	<div class="kv-wrap kv-crew">
		<div><p class="kv-tag">Опт и объекты</p><h2 class="kv-h2" id="kv-crew-title">Бригадам и строителям</h2></div>
		<ul class="kv-crew__list">
			<li>Оптовые и повторные заказы</li>
			<li>Работаем по безналичному расчёту</li>
			<li>Рассчитаем каркасы под ваш объект</li>
		</ul>
		<p class="kv-crew__go">Позвоните или оставьте заявку — обсудим объём.<span><?php echo kv_contact_link( 'phone', 'kv-btn kv-btn--ghost' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?><a class="kv-btn" href="#zayavka">Оставить заявку</a></span></p>
	</div>
</section>

<?php
kv_order_form();
get_footer();
