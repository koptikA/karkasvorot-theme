<?php
/** Visual home: product selection, real work and individual quotation. */
get_header();
$kv_uri = get_template_directory_uri();
?>
<div class="kv-container kv-section kv-home kv-home--visual">
 <section class="kv-intro" aria-labelledby="kv-intro-title">
  <div class="kv-intro__copy">
   <p class="kv-eyebrow">Мастерская металлоконструкций · Минск</p>
   <h1 id="kv-intro-title">Каркасы ворот.<br><span>Под ваш размер.</span></h1>
   <p class="kv-intro__lead">Изготовим, доставим и смонтируем.<br>В Минске и Минской области.</p>
   <a class="kv-button" href="#zayavka">Рассчитать мой заказ</a>
   <a class="kv-intro__link" href="#raboty">Посмотреть работы <span aria-hidden="true">↓</span></a>
  </div>
  <figure class="kv-intro__photo">
   <img src="<?php echo esc_url( $kv_uri . '/assets/work-gates.webp' ); ?>" width="516" height="265" alt="Ворота с калиткой из металлоштакетника — выполненная работа" fetchpriority="high" loading="eager">
   <figcaption><span>Сделано в нашей мастерской</span><span aria-hidden="true">↗</span></figcaption>
  </figure>
 </section>
 <section class="kv-select" aria-labelledby="kv-select-title">
  <div class="kv-home-heading"><h2 id="kv-select-title">Что изготовим для вас?</h2><p>От каркаса — до готового изделия</p></div>
  <div class="kv-select__grid">
   <?php
   $kv_labels = array( 'karkas-otkatnyh-vorot' => 'Откатные ворота', 'karkas-raspashnyh-vorot' => 'Распашные ворота', 'kalitki' => 'Калитки', 'prochie-izdeliya' => 'Другие изделия' );
   foreach ( kv_service_pages() as $kv_page ) :
    $kv_ill = (int) get_post_meta( $kv_page->ID, '_kv_illustration', true );
    $kv_label = isset( $kv_labels[ $kv_page->post_name ] ) ? $kv_labels[ $kv_page->post_name ] : get_the_title( $kv_page );
   ?>
   <a class="kv-select__card" href="<?php echo esc_url( get_permalink( $kv_page ) ); ?>">
    <div class="kv-select__image"><?php echo wp_get_attachment_image( $kv_ill, 'kv-card', false, array( 'loading' => 'lazy', 'alt' => 'Иллюстрация: ' . $kv_label ) ); ?></div>
    <h3><?php echo esc_html( $kv_label ); ?><span aria-hidden="true">↗</span></h3>
   </a>
   <?php endforeach; ?>
  </div>
  <p class="kv-select__note">Стоимость рассчитывается индивидуально — по размерам и комплектации.</p>
 </section>
 <section class="kv-showcase" id="raboty" aria-labelledby="kv-showcase-title">
  <div class="kv-home-heading"><div><p class="kv-eyebrow">Из металла — в жизнь</p><h2 id="kv-showcase-title">Вот что получается.</h2></div><a href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Все работы <span aria-hidden="true">↗</span></a></div>
  <div class="kv-showcase__grid">
   <?php foreach ( kv_works( 0, 3 ) as $kv_work ) { get_template_part( 'template-parts/work-card', null, array( 'work' => $kv_work ) ); } ?>
  </div>
 </section>
 <section class="kv-process" aria-labelledby="kv-process-title">
  <h2 id="kv-process-title">От идеи до установки.</h2>
  <ol>
   <li><span aria-hidden="true">01</span><div><h3>Расскажите, что нужно</h3><p>Оставьте телефон и размеры, если знаете.</p></div></li>
   <li><span aria-hidden="true">02</span><div><h3>Согласуем детали</h3><p>Подберём конструкцию и рассчитаем стоимость.</p></div></li>
   <li><span aria-hidden="true">03</span><div><h3>Изготовим и доставим</h3><p>Монтаж — по договорённости.</p></div></li>
  </ol>
 </section>
 <?php kv_order_form(); ?>
</div>
<?php get_footer(); ?>
