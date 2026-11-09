<?php
/**
 * Installer: first content (pages, texts, prices), menu, works from the media library, drafts of the old site.
 * Runs on theme activation; a repeat run creates nothing twice and never deletes anything.
 */

defined( 'ABSPATH' ) || exit;

/** Wrap plain HTML into core blocks: <p> → paragraph, <h2> → heading, <ul> → list. */
function kv_blocks( string $html ): string {
	$out = '';
	foreach ( preg_split( '/\n+/', trim( $html ) ) as $line ) {
		$line = trim( $line );
		if ( 0 === strpos( $line, '<h2' ) ) {
			$id   = preg_match( '/id="([^"]+)"/', $line, $m ) ? '{"anchor":"' . $m[1] . '"} ' : '';
			$line = str_replace( '<h2', '<h2 class="wp-block-heading"', $line );
			$out .= "<!-- wp:heading {$id}-->\n{$line}\n<!-- /wp:heading -->\n\n";
		} elseif ( 0 === strpos( $line, '<ul' ) ) {
			$line = str_replace( '<li>', "<!-- wp:list-item -->\n<li>", str_replace( '</li>', "</li>\n<!-- /wp:list-item -->", $line ) );
			$out .= "<!-- wp:list -->\n" . str_replace( '<ul>', '<ul class="wp-block-list">', $line ) . "\n<!-- /wp:list -->\n\n";
		} elseif ( '' !== $line ) {
			$out .= "<!-- wp:paragraph -->\n{$line}\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $out;
}

/** Owner's geography (2026-11-09): montage in Minsk and the region, frames delivered across Belarus. Old texts → new ones. */
function kv_install_zone(): array {
	return array(
		'zone'      => 'Изготавливаем в Минске. Монтируем в Минске и Минской области, в том числе в Червенском районе. Каркасы доставляем по всей Беларуси.',
		'mount'     => 'Да, монтируем в Минске и Минской области, в том числе в Червенском районе. В другие регионы Беларуси доставляем каркасы.',
		'old_zone'  => 'Изготавливаем в Минске, доставляем и монтируем по всей Беларуси — условия выезда согласуем при заказе.',
		'old_mount' => 'Да, мы занимаемся изготовлением и монтажом. Работаем в Минске и по всей Беларуси: доставку и выезд на монтаж в другой город согласуем при заказе.',
	);
}

/** «Продаём только каркас» (owner, 2026-11-09). */
function kv_install_frame_faq(): array {
	return array( 'q' => 'Продаёте ли ролики, направляющую, автоматику?', 'a' => 'Нет, мы изготавливаем только каркас под ваш проём. Ролики, направляющую, автоматику и другую фурнитуру нужно купить отдельно.' );
}

/** «Прочие изделия» → «Навесы и беседки»: car canopies live on their own page. */
function kv_install_navesy_link(): string {
	return '<p>Беседки на металлическом каркасе изготавливаем и монтируем. Навесы для машин — на отдельной странице «<a href="' . esc_url( home_url( '/navesy-dlya-avto/' ) ) . '">Навесы для авто</a>».</p>';
}

/** Pages: slug => [title, template, content, meta]. Texts only from known facts (spec «Данные и контент»). */
function kv_install_pages(): array {
	$zone    = kv_install_zone()['zone'];
	$order   = array( 'q' => 'Как сделать заказ?', 'a' => 'Оставьте заявку на сайте или позвоните. Мастер перезвонит, согласует вид, высоту и длину каркаса, после этого каркас изготавливается, доставляется или монтируется.' );
	$mount   = array( 'q' => 'Вы делаете монтаж?', 'a' => kv_install_zone()['mount'] );
	$frame   = kv_install_frame_faq();
	$service = function ( $what, $price, $price_note ) use ( $zone, $order, $mount, $frame ) {
		return array(
			'<p>Изготавливаем и монтируем ' . $what . '. Каркас сваривается из профильной трубы 60×40×2 мм и покрывается грунтовкой. ' . $zone . '</p>',
			array(
				array( 'q' => 'Сколько стоит ' . $price_note . '?', 'a' => 'От ' . $price . ' BYN. Точную цену мастер назовёт после согласования вида, высоты и длины каркаса.' ),
				array( 'q' => 'Из чего делается каркас?', 'a' => 'Из профильной трубы 60×40×2 мм. Готовый каркас покрывается грунтовкой.' ),
				$frame,
				$mount,
				$order,
			),
		);
	};
	list( $otk, $otk_faq ) = $service( 'каркасы откатных ворот', '1000', 'каркас откатных ворот (проём 3×1,5 м)' );
	list( $ras, $ras_faq ) = $service( 'каркасы распашных ворот', '500', 'каркас распашных ворот' );
	list( $kal, $kal_faq ) = $service( 'каркасы калиток', '270', 'каркас калитки' );
	$tube = "труба 60×40×2 мм\nгрунтовка";

	return array(
		'glavnaya'                   => array( 'Главная', '', '<p>Изготавливаем и монтируем каркасы откатных и распашных ворот, калиток и другие металлоконструкции из профильной трубы. ' . $zone . '</p>', array() ),
		'karkas-otkatnyh-vorot'      => array( 'Каркас откатных ворот', 'page-service.php', $otk, array( 'price' => '1000', 'includes' => "каркас под проём 3×1,5 м\n" . $tube, 'file' => 'vorota.png', 'faq' => $otk_faq ) ),
		'karkas-raspashnyh-vorot'    => array( 'Каркас распашных ворот', 'page-service.php', $ras, array( 'price' => '500', 'includes' => $tube, 'file' => 'raspashnye-vorota.png', 'faq' => $ras_faq ) ),
		'kalitki'                    => array( 'Калитки', 'page-service.php', $kal, array( 'price' => '270', 'includes' => $tube, 'file' => 'kalitka.png', 'faq' => $kal_faq ) ),
		'navesy-dlya-avto'           => array(
			'Навесы для авто',
			'page-service.php',
			'<p>Изготавливаем навесы для машин на металлическом каркасе. Каркас варим в своей мастерской в Минске.</p>
<p>Монтируем навесы в Минске и Минской области, в том числе в Червенском районе. Каркасы доставляем по всей Беларуси.</p>
<p>Стоимость — по запросу: пришлите ширину и длину площадки под навес, мастер перезвонит, уточнит детали и посчитает.</p>',
			array(
				'order'    => 3, // right after «Калитки», also when the other pages already exist
				'price'    => '',
				'includes' => "металлический каркас навеса
изготовление в Минске
монтаж в Минске и Минской области",
				'file'     => 'naves-dlya-mashin-karkas-vorot-by.jpg',
				'faq'      => array(
					array( 'q' => 'Сколько стоит навес для авто?', 'a' => 'Стоимость по запросу: она зависит от размеров и конструкции навеса. Оставьте заявку или позвоните — мастер уточнит детали и посчитает.' ),
					array( 'q' => 'Что нужно для расчёта?', 'a' => 'Ширина и длина площадки под навес и населённый пункт. Размеры можно указать в калькуляторе на этой странице — они попадут в заявку.' ),
					array( 'q' => 'Где вы монтируете навесы?', 'a' => kv_install_zone()['mount'] ),
					$order,
				),
			),
		),
		'prochie-izdeliya'           => array(
			'Прочие изделия',
			'page-service.php',
			'<p>Кроме каркасов ворот и калиток, изготавливаем другие металлоконструкции: каркасы мебели, обувницы, заборы, навесы и беседки. ' . $zone . '</p>
<h2 id="mebel">Каркасы мебели</h2>
<p>Сварные каркасы для столов, тумб и стеллажей из профильной трубы 20×20×2 мм, покрытые грунтовкой. Вид и размеры согласовываем при заказе.</p>
<h2 id="obuvnicy">Обувницы</h2>
<p>Сварные обувницы из профильной трубы 40×40×2 мм, покрытые грунтовкой.</p>
<h2 id="zabory">Заборы</h2>
<p>Заборы из профнастила и металлоштакета: изготовление и монтаж.</p>
<h2 id="navesy">Навесы и беседки</h2>
' . kv_install_navesy_link(),
			array(
				'price'    => '300',
				'includes' => "каркасы мебели — труба 20×20×2 мм\nобувницы — труба 40×40×2 мм\nгрунтовка",
				'file'     => 'tumba.png',
				'prices'   => array( 'mebel' => '300', 'obuvnicy' => '300', 'zabory' => '', 'navesy' => '' ),
				'faq'      => array(
					array( 'q' => 'Сколько стоит каркас мебели или обувница?', 'a' => 'От 300 BYN. Точную цену мастер назовёт после согласования вида и размеров.' ),
					array( 'q' => 'Сколько стоят забор, навес или беседка?', 'a' => 'Цена по запросу: оставьте заявку, мастер свяжется с вами и рассчитает стоимость.' ),
					array( 'q' => 'Из какой трубы делаются каркасы мебели и обувницы?', 'a' => 'Каркасы мебели — из профильной трубы 20×20×2 мм, обувницы — из трубы 40×40×2 мм. Готовые изделия покрываются грунтовкой.' ),
					$order,
				),
			),
		),
		'kontakty'                   => array( 'Контакты', '', '<p>Позвоните, напишите в Viber или Telegram или оставьте заявку — мастер перезвонит. ' . $zone . '</p>', array() ),
		'politika-konfidencialnosti' => array( 'Политика обработки персональных данных', '', kv_policy_html(), array() ),
	);
}

function kv_policy_html(): string {
	return '<p>Политика определяет, как обрабатываются персональные данные посетителей сайта karkasvorot.by, в соответствии с Законом Республики Беларусь от 7 мая 2021 г. № 99-З «О защите персональных данных».</p>
<h2>Оператор</h2>
<p>[kv_operator]</p>
<h2>Цели обработки</h2>
<ul><li>обработка заявки на изготовление изделия;</li><li>обратный звонок для согласования заказа.</li></ul>
<h2>Какие данные обрабатываются</h2>
<p>Имя, номер телефона, выбранное изделие и комментарий, которые вы указываете в форме заявки. Данные обрабатываются с вашего согласия: его вы даёте отметкой в форме перед отправкой.</p>
<h2>Срок хранения</h2>
<p>Данные заявки хранятся до достижения целей обработки или до отзыва согласия, после чего удаляются.</p>
<h2>Ваши права</h2>
<p>Вы можете получить информацию об обработке своих данных, потребовать их изменения, прекращения обработки или удаления, отозвать согласие, а также обжаловать действия оператора в уполномоченный орган по защите прав субъектов персональных данных.</p>
<h2>Как отозвать согласие</h2>
<p>Чтобы отозвать согласие или воспользоваться другими правами, свяжитесь с оператором по контактам на странице «Контакты» или в подвале сайта. После отзыва согласия обработка данных прекращается, а данные удаляются.</p>
<h2>Cookie и Яндекс Метрика</h2>
<p>Сайт использует сервис веб-аналитики Яндекс Метрика только после того, как вы нажмёте «Принять» в баннере cookie. Метрика собирает обезличенные сведения о посещениях: просмотренные страницы, тип устройства и браузера. Эти данные обрабатывает Яндекс, в том числе на серверах за пределами Республики Беларусь. Если нажать «Отклонить», Метрика не загружается. Выбор хранится в вашем браузере; чтобы изменить его, очистите данные сайта в настройках браузера.</p>';
}

/** Policy operator: requisites from the Customizer; empty → contacts for visitors, a hint for admins. */
add_shortcode( 'kv_operator', function () {
	$parts = array();
	foreach ( array( 'org_name' => '', 'unp' => 'УНП ', 'address' => 'адрес: ', 'registration' => '' ) as $key => $prefix ) {
		if ( '' !== kv_opt( $key ) ) {
			$parts[] = $prefix . kv_opt( $key );
		}
	}
	$contacts = array_filter( array( kv_opt( 'phone' ) ? 'телефон ' . kv_opt( 'phone' ) : '', kv_opt( 'email' ) ? 'e-mail ' . antispambot( kv_opt( 'email' ) ) : '' ) );
	$out      = esc_html( ( $parts ? 'Оператор: ' . implode( ', ', $parts ) : 'Оператор — владелец сайта karkasvorot.by' ) . ( $contacts ? '; ' . implode( ', ', $contacts ) . '.' : '.' ) );
	if ( kv_is_placeholder( 'org_name' ) && current_user_can( 'manage_options' ) ) {
		$out .= ' <mark class="kv-placeholder">Заполните реквизиты оператора: Внешний вид → Настроить → Реквизиты</mark>';
	}
	return $out;
} );

/** Works: file in the media library => [service page slug, «Прочие» section, optional: also shown in these service pages]. */
function kv_install_works(): array {
	return array(
		'kovanye-vorota-s-kirpichnymi-stolbami.jpg' => array( 'karkas-raspashnyh-vorot', '', array( 'kalitki' ) ),
		'frame-125.jpg'                             => array( 'karkas-raspashnyh-vorot', '', array( 'kalitki' ) ),
		'raspashnye-vorota-s-kalitkoj.png'          => array( 'prochie-izdeliya', 'zabory', array( 'kalitki' ) ),
		'zabor-iz-profnastila-bordo-cena.jpg'       => array( 'prochie-izdeliya', 'zabory' ),
		'naves-dlya-mashin-karkas-vorot-by.jpg'     => array( 'navesy-dlya-avto', 'navesy', array( 'prochie-izdeliya' ) ),
		'sadovaya-bosedka-barbekyu-karkas.jpg'      => array( 'prochie-izdeliya', 'navesy' ),
		'slider10.jpg'                              => array( 'prochie-izdeliya', 'obuvnicy' ),
		'slider9.jpg'                               => array( 'prochie-izdeliya', 'mebel' ),
		'slider8.jpg'                               => array( 'prochie-izdeliya', 'mebel' ),
		'slider7.jpg'                               => array( 'prochie-izdeliya', 'mebel' ),
		'slider1.jpg'                               => array( 'karkas-otkatnyh-vorot', '' ), // owner: a sliding gate
		'slider2.jpg'                               => array( '', '' ),
		'slider4.png'                               => array( 'karkas-raspashnyh-vorot', '' ),
	);
}

/** Attachment ID by file name in _wp_attached_file (any upload folder, also the "-scaled" copy); 0 if none. */
function kv_attachment_by_file( string $file ): int {
	global $wpdb;
	$dot    = strrpos( $file, '.' );
	$scaled = substr( $file, 0, $dot ) . '-scaled' . substr( $file, $dot );
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value IN ( %s, %s ) OR meta_value LIKE %s OR meta_value LIKE %s ) ORDER BY post_id LIMIT 1",
		$file,
		$scaled,
		'%/' . $wpdb->esc_like( $file ),
		'%/' . $wpdb->esc_like( $scaled )
	) );
}

/** Owner's improved photos (assets/works/, product unchanged): file => [legacy source of the work, alt]. '' = a new work. */
function kv_install_photos(): array {
	return array(
		'naves-dlya-avto.jpg'              => array( 'naves-dlya-mashin-karkas-vorot-by.jpg', 'Навес для машины на металлическом каркасе с арочной крышей у частного дома' ),
		'zabor-shtaketnik-temnyy.jpg'      => array( 'frame-125.jpg', 'Тёмный забор из металлоштакетника перед частным домом' ),
		'stolik-loft-v-interere.jpg'       => array( 'slider8.jpg', 'Металлический столик в стиле лофт между мягкими банкетками в прихожей' ),
		'karkas-stola-geometricheskiy.jpg' => array( 'slider9.jpg', 'Сварной каркас стола с геометрическими вставками на полу мастерской' ),
		'vorota-shtaketnik-svetlye.jpg'    => array( 'slider1.jpg', 'Ворота из светлого металлоштакетника на участке' ),
		'vorota-chernye-prutok-u-doma.jpg' => array( 'slider4.png', 'Чёрные металлические ворота из вертикальных прутьев у кирпичного дома' ),
		'zelenyy-zabor-s-kalitkoy.jpg'     => array( 'raspashnye-vorota-s-kalitkoj.png', 'Зелёный забор из профнастила с калиткой' ),
		'obuvnitsa-svarnaya.jpg'           => array( 'slider10.jpg', 'Сварная металлическая обувница с декоративными квадратами на боковинах' ),
		'mangal-na-podstavke.jpg'          => array( '', 'Металлический мангал на подставке с боковыми полками во дворе' ),
	);
}

/** Media library ID of a theme photo: found by our meta or file name, else copied in (new file, sub-sizes). 0 on failure. */
function kv_install_photo( string $file, string $alt ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 'meta_key' => '_kv_photo', 'meta_value' => $file, 'fields' => 'ids', 'numberposts' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	$id    = $found ? (int) $found[0] : kv_attachment_by_file( $file );
	if ( $id ) {
		return $id;
	}
	$path = KV_DIR . '/assets/works/' . $file;
	$up   = is_readable( $path ) ? wp_upload_bits( $file, null, (string) file_get_contents( $path ) ) : array( 'error' => 'no file' );
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment( array( 'post_title' => $alt, 'post_mime_type' => 'image/jpeg', 'post_status' => 'inherit' ), $up['file'] );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_kv_photo', $file );
	return (int) $id;
}

/** Work ID with _kv_source = $source; 0 if none. */
function kv_install_work_of( int $source ): int {
	$w = $source ? get_posts( array( 'post_type' => 'work', 'post_status' => 'any', 'meta_key' => '_kv_source', 'meta_value' => (string) $source, 'fields' => 'ids', 'numberposts' => 1 ) ) : array(); // phpcs:ignore WordPress.DB.SlowDBQuery
	return $w ? (int) $w[0] : 0;
}

/** New photos become work thumbnails (only while the work still shows its legacy image); old attachments stay. */
function kv_install_owner_photos( array $ids, array &$report ) {
	foreach ( kv_install_photos() as $file => $p ) {
		list( $old, $alt ) = $p;
		$source = '' !== $old ? kv_attachment_by_file( $old ) : 0;
		$work   = kv_install_work_of( $source );
		if ( '' !== $old && ! $work ) {
			$report['skipped'][] = 'photo:' . $file . ' (нет работы ' . $old . ')';
			continue;
		}
		if ( $work && ! in_array( (int) get_post_thumbnail_id( $work ), array( 0, $source ), true ) ) {
			continue; // already the new photo, or the owner chose another one
		}
		$att = kv_install_photo( $file, $alt );
		if ( ! $att ) {
			$report['skipped'][] = 'photo:' . $file . ' (не загрузилось)';
			continue;
		}
		if ( $work ) {
			set_post_thumbnail( $work, $att );
			$report['created'][] = 'photo:' . $file;
		} elseif ( ! kv_install_work_of( $att ) && ! empty( $ids['prochie-izdeliya'] ) ) {
			$id = wp_insert_post( wp_slash( array( 'post_type' => 'work', 'post_status' => 'publish', 'post_title' => 'Мангал на подставке', 'post_name' => 'mangal-na-podstavke', 'menu_order' => 100 ) ) );
			if ( $id && ! is_wp_error( $id ) ) {
				set_post_thumbnail( $id, $att );
				update_post_meta( $id, '_kv_source', (string) $att );
				update_post_meta( $id, '_kv_service', (string) $ids['prochie-izdeliya'] );
				$report['created'][] = 'work:' . $file;
			}
		}
	}
}

/** Sites installed before 2026-11-09: works to their new pages, owner's geography, frame-only FAQ. Exact old texts only. */
function kv_install_migrate( array $ids ) {
	$bind = function ( int $work, string $slug ) use ( $ids ) {
		if ( $work && ! empty( $ids[ $slug ] ) ) {
			update_post_meta( $work, '_kv_service', (string) $ids[ $slug ] );
			delete_post_meta( $work, '_kv_section' );
		}
	};
	foreach ( array( 'slider1.jpg' => 'karkas-otkatnyh-vorot', 'slider4.png' => 'karkas-raspashnyh-vorot' ) as $file => $slug ) {
		$w = kv_install_work_of( kv_attachment_by_file( $file ) );
		if ( $w && ! get_post_meta( $w, '_kv_service', true ) ) {
			$bind( $w, $slug );
		}
	}
	$w = kv_install_work_of( kv_attachment_by_file( 'naves-dlya-mashin-karkas-vorot-by.jpg' ) );
	if ( $w && 'navesy' === get_post_meta( $w, '_kv_section', true ) && ! empty( $ids['navesy-dlya-avto'] ) && (int) get_post_meta( $w, '_kv_service', true ) !== (int) $ids['navesy-dlya-avto'] ) {
		$bind( $w, 'navesy-dlya-avto' );
	}

	$zone = kv_install_zone();
	$from = array( $zone['old_zone'], '<p>Навесы и беседки на металлическом каркасе: изготовление и монтаж.</p>' );
	$to   = array( $zone['zone'], kv_install_navesy_link() );
	foreach ( array( 'glavnaya', 'karkas-otkatnyh-vorot', 'karkas-raspashnyh-vorot', 'kalitki', 'prochie-izdeliya', 'kontakty' ) as $slug ) {
		if ( empty( $ids[ $slug ] ) ) {
			continue;
		}
		$content = (string) get_post_field( 'post_content', $ids[ $slug ], 'raw' );
		$new     = str_replace( $from, $to, $content );
		if ( $new !== $content ) {
			wp_update_post( wp_slash( array( 'ID' => $ids[ $slug ], 'post_content' => $new ) ) );
		}
		$faq = (string) get_post_meta( $ids[ $slug ], '_kv_faq', true );
		if ( false !== strpos( $faq, $zone['old_mount'] ) ) {
			kv_set_meta( $ids[ $slug ], '_kv_faq', str_replace( $zone['old_mount'], $zone['mount'], $faq ) );
		}
	}

	if ( get_option( 'kv_migrated_1109' ) ) {
		return; // the frame-only answer is added once; if the owner removes it, it stays removed
	}
	$frame = kv_install_frame_faq();
	foreach ( array( 'karkas-otkatnyh-vorot', 'karkas-raspashnyh-vorot', 'kalitki' ) as $slug ) {
		$items = empty( $ids[ $slug ] ) ? null : json_decode( (string) get_post_meta( $ids[ $slug ], '_kv_faq', true ), true );
		if ( is_array( $items ) && ! in_array( $frame['q'], wp_list_pluck( $items, 'q' ), true ) ) {
			array_splice( $items, min( 2, count( $items ) ), 0, array( $frame ) );
			kv_set_meta( $ids[ $slug ], '_kv_faq', wp_json_encode( $items, JSON_UNESCAPED_UNICODE ) );
		}
	}
	update_option( 'kv_migrated_1109', KV_VERSION, false );
}

/**
 * Works in several sections (owner, 2026-11-09), once per site: slider1 from swing to sliding gates, extra sections
 * from kv_install_works(), the carport also in «Прочие → Навесы». Only adds what is missing; owner's later edits stay.
 */
function kv_install_links( array $ids ) {
	if ( get_option( 'kv_migrated_also' ) ) {
		return;
	}
	$works = kv_install_works();
	$work  = function ( string $file ) {
		return kv_install_work_of( kv_attachment_by_file( $file ) );
	};
	$w = $work( 'slider1.jpg' );
	if ( $w && ! empty( $ids['karkas-raspashnyh-vorot'] ) && ! empty( $ids['karkas-otkatnyh-vorot'] ) && (int) get_post_meta( $w, '_kv_service', true ) === (int) $ids['karkas-raspashnyh-vorot'] ) {
		update_post_meta( $w, '_kv_service', (string) $ids['karkas-otkatnyh-vorot'] );
	}
	$w = $work( 'naves-dlya-mashin-karkas-vorot-by.jpg' );
	if ( $w && ! get_post_meta( $w, '_kv_section', true ) ) {
		update_post_meta( $w, '_kv_section', 'navesy' );
	}
	foreach ( $works as $file => $target ) {
		$w = empty( $target[2] ) ? 0 : $work( $file );
		if ( ! $w ) {
			continue;
		}
		$have = array_map( 'intval', get_post_meta( $w, '_kv_also' ) );
		foreach ( $target[2] as $slug ) {
			$page = empty( $ids[ $slug ] ) ? 0 : (int) $ids[ $slug ];
			if ( $page && ! in_array( $page, $have, true ) && (int) get_post_meta( $w, '_kv_service', true ) !== $page ) {
				add_post_meta( $w, '_kv_also', (string) $page );
				$have[] = $page;
			}
		}
	}
	update_option( 'kv_migrated_also', KV_VERSION, false );
}

/** @return array{created:array,skipped:array,drafted:array} */
function kv_install(): array {
	$report    = array( 'created' => array(), 'skipped' => array(), 'drafted' => array() );
	$attention = array();
	$ids       = array();
	$menu      = 0;
	$failed    = false;
	foreach ( kv_install_pages() as $slug => $p ) {
		list( $title, $template, $content, $meta ) = $p;
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$ids[ $slug ]        = $existing->ID;
			$report['skipped'][] = 'page:' . $slug;
			// Someone else's page under our slug: never overwrite it, tell the owner instead.
			if ( $template && ( get_page_template_slug( $existing ) !== $template || ( ! empty( $meta['price'] ) && ! metadata_exists( 'post', $existing->ID, '_kv_price_from' ) ) || ( isset( $meta['prices'] ) && ! metadata_exists( 'post', $existing->ID, '_kv_prices' ) ) ) ) {
				$attention[] = 'page:' . $slug . ' — страница уже была, без шаблона «Услуга» или полей цены; содержимое не менялось';
			}
			continue;
		}
		$id = wp_insert_post( wp_slash( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => kv_blocks( $content ),
			'menu_order'   => isset( $meta['order'] ) ? $meta['order'] : $menu,
		) ) );
		$menu++;
		if ( ! $id || is_wp_error( $id ) ) {
			$failed      = true;
			$attention[] = 'page:' . $slug . ' — не удалось создать; главная и меню не настраивались';
			continue;
		}
		$ids[ $slug ]        = $id;
		$report['created'][] = 'page:' . $slug;
		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}
		if ( isset( $meta['price'] ) ) {
			kv_set_meta( $id, '_kv_price_from', $meta['price'] );
			kv_set_meta( $id, '_kv_includes', $meta['includes'] );
			kv_set_meta( $id, '_kv_faq', wp_json_encode( $meta['faq'], JSON_UNESCAPED_UNICODE ) );
			$ill = kv_attachment_by_file( $meta['file'] );
			kv_set_meta( $id, '_kv_illustration', $ill ? (string) $ill : '' );
		}
		if ( isset( $meta['prices'] ) ) {
			kv_set_meta( $id, '_kv_prices', wp_json_encode( $meta['prices'], JSON_UNESCAPED_UNICODE ) );
		}
	}

	$front = $failed || empty( $ids['glavnaya'] ) ? '' : get_post_status( $ids['glavnaya'] );
	if ( 'publish' === $front && ( in_array( 'page:glavnaya', $report['created'], true ) || 'page' !== get_option( 'show_on_front' ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['glavnaya'] );
	}
	update_option( 'wp_attachment_pages_enabled', '0' );

	$locations = get_nav_menu_locations();
	if ( $failed ) {
		$report['skipped'][] = 'menu:primary';
	} elseif ( empty( $locations['primary'] ) || ! wp_get_nav_menu_object( $locations['primary'] ) ) {
		$menu_obj = wp_get_nav_menu_object( 'Главное меню' );
		$menu_id  = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu( 'Главное меню' );
		if ( ! is_wp_error( $menu_id ) ) {
			if ( ! $menu_obj ) {
				$items = array( 'karkas-otkatnyh-vorot' => 'Откатные', 'karkas-raspashnyh-vorot' => 'Распашные', 'kalitki' => 'Калитки', 'prochie-izdeliya' => 'Прочие изделия', 'work' => 'Наши работы', 'kontakty' => 'Контакты' );
				foreach ( $items as $slug => $label ) {
					$item = 'work' === $slug
						? array( 'menu-item-type' => 'post_type_archive', 'menu-item-object' => 'work' )
						: array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $ids[ $slug ] );
					wp_update_nav_menu_item( $menu_id, 0, $item + array( 'menu-item-title' => $label, 'menu-item-status' => 'publish' ) );
				}
				$report['created'][] = 'menu:primary';
			}
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	} else {
		$report['skipped'][] = 'menu:primary';
	}

	$order = 0;
	foreach ( kv_install_works() as $file => $target ) {
		$order++;
		$att = kv_attachment_by_file( $file );
		if ( ! $att ) {
			$report['skipped'][] = 'work:' . $file . ' (нет файла)';
			continue;
		}
		$exists = get_posts( array( 'post_type' => 'work', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ), 'meta_key' => '_kv_source', 'meta_value' => (string) $att, 'fields' => 'ids', 'numberposts' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $exists ) {
			$report['skipped'][] = 'work:' . $file;
			continue;
		}
		$alt   = trim( (string) get_post_meta( $att, '_wp_attachment_image_alt', true ), " \"'«»" );
		$title = '' !== $alt ? $alt : get_the_title( $att );
		$names = kv_catalog_images();
		if ( preg_match( '/^slider\d+$/i', $title ) && isset( $names[ $file ] ) ) { $title = $names[ $file ][1]; }
		$id    = wp_insert_post( wp_slash( array( 'post_type' => 'work', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => isset( $names[ $file ] ) ? $names[ $file ][0] : '', 'menu_order' => $order ) ) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		set_post_thumbnail( $id, $att );
		update_post_meta( $id, '_kv_source', (string) $att );
		if ( '' !== $target[0] && ! empty( $ids[ $target[0] ] ) ) {
			update_post_meta( $id, '_kv_service', (string) $ids[ $target[0] ] );
			kv_set_meta( $id, '_kv_section', $target[1] );
		}
		$report['created'][] = 'work:' . $file;
	}

	if ( ! $failed ) {
		kv_install_owner_photos( $ids, $report );
		kv_install_migrate( $ids );
		kv_install_links( $ids );
	}

	$old = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1 ) );
	foreach ( $old as $post ) {
		if ( '' === trim( $post->post_title ) || 0 === strpos( $post->post_title, 'Слайд' ) ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'draft' ) );
			$report['drafted'][] = 'post:' . $post->ID . ' «' . $post->post_title . '»';
		}
	}
	$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );
	if ( $sample && 'publish' === $sample->post_status ) {
		wp_update_post( array( 'ID' => $sample->ID, 'post_status' => 'draft' ) );
		$report['drafted'][] = 'page:sample-page';
	}

	update_option( 'kv_install_report', $report + array( 'attention' => $attention, 'time' => current_time( 'mysql' ) ), false );
	kv_refresh_imported_work_names();
	update_option( 'kv_install_version', KV_VERSION, false );
	return $report;
}

add_action( 'after_switch_theme', 'kv_install' );
