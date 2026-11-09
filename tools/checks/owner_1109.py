"""Owner answers 2026-11-09: improved work photos, «Навесы для авто» page + calculator tab, brigades block,
frame-only FAQ, works filter without empty services."""
import json
import re

from stand import fetch, run_php

NAVESY = "/navesy-dlya-avto/"
PHOTOS = {  # new file in the theme -> legacy source of the work
    "naves-dlya-avto.jpg": "naves-dlya-mashin-karkas-vorot-by.jpg",
    "zabor-shtaketnik-temnyy.jpg": "frame-125.jpg",
    "stolik-loft-v-interere.jpg": "slider8.jpg",
    "karkas-stola-geometricheskiy.jpg": "slider9.jpg",
    "vorota-shtaketnik-svetlye.jpg": "slider1.jpg",
    "vorota-chernye-prutok-u-doma.jpg": "slider4.png",
    "zelenyy-zabor-s-kalitkoy.jpg": "raspashnye-vorota-s-kalitkoj.png",
}
FRAME_Q = "Продаёте ли ролики, направляющую, автоматику?"
# Before the owner's answer: «доставка и монтаж по всей Беларуси», «выезд на монтаж … согласуем».
OLD_GEO = re.compile(r"(?:доставка и монтаж|доставляем и монтируем|монтаж\w*)\s+(?:—\s+)?(?:по\s+)?вс[ея]й? Беларус\w*|выезд\w* на монтаж", re.I)

STATE = r"""
$works = array();
foreach ( %s as $new => $old ) {
	$src = kv_attachment_by_file( $old );
	$w   = get_posts( array( 'post_type' => 'work', 'post_status' => 'any', 'meta_key' => '_kv_source', 'meta_value' => (string) $src, 'fields' => 'ids', 'numberposts' => 1 ) );
	$th  = $w ? (int) get_post_thumbnail_id( $w[0] ) : 0;
	$works[ $new ] = array( 'src' => $src, 'thumb' => $th, 'file' => wp_basename( (string) get_post_meta( $th, '_wp_attached_file', true ) ),
		'alt' => (string) get_post_meta( $th, '_wp_attachment_image_alt', true ), 'src_alive' => 'attachment' === get_post_type( $src ),
		'service' => $w ? get_post_field( 'post_name', (int) get_post_meta( $w[0], '_kv_service', true ) ) : '' );
}
$m = get_posts( array( 'post_type' => 'work', 'post_status' => 'publish', 'title' => 'Мангал на подставке', 'fields' => 'ids', 'numberposts' => -1 ) );
$count = function () {
	return count( get_posts( array( 'post_type' => array( 'attachment', 'work', 'page' ), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) );
};
echo wp_json_encode( array(
	'works'  => $works,
	'mangal' => array( count( $m ), $m ? get_post_field( 'post_name', (int) get_post_meta( $m[0], '_kv_service', true ) ) : '', $m ? wp_basename( (string) get_post_meta( (int) get_post_thumbnail_id( $m[0] ), '_wp_attached_file', true ) ) : '' ),
	'count'  => $count(),
) );
"""

# A site installed before this update: old thumbnails, no service on slider1, FAQ without the frame-only answer.
LIVE = r"""
wp_set_current_user( 1 );
$src  = kv_attachment_by_file( 'slider1.jpg' );
$w    = get_posts( array( 'post_type' => 'work', 'meta_key' => '_kv_source', 'meta_value' => (string) $src, 'fields' => 'ids', 'numberposts' => 1 ) )[0];
$k    = get_page_by_path( 'kalitki' )->ID;
$faq  = json_decode( get_post_meta( $k, '_kv_faq', true ), true );
$keep = array_values( array_filter( $faq, function ( $i ) { return false === strpos( $i['q'], 'ролики' ); } ) );
set_post_thumbnail( $w, $src );
delete_post_meta( $w, '_kv_service' );
update_post_meta( $k, '_kv_faq', wp_slash( wp_json_encode( $keep, JSON_UNESCAPED_UNICODE ) ) );
delete_option( 'kv_migrated_1109' );
$n = count( get_posts( array( 'post_type' => array( 'attachment', 'work', 'page' ), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) );
kv_install();
kv_install();
$faq = json_decode( get_post_meta( $k, '_kv_faq', true ), true );
echo wp_json_encode( array(
	'thumb'   => wp_basename( (string) get_post_meta( (int) get_post_thumbnail_id( $w ), '_wp_attached_file', true ) ),
	'service' => get_post_field( 'post_name', (int) get_post_meta( $w, '_kv_service', true ) ),
	'frame_q' => count( array_filter( $faq, function ( $i ) { return false !== strpos( $i['q'], 'ролики' ); } ) ),
	'grew'    => count( get_posts( array( 'post_type' => array( 'attachment', 'work', 'page' ), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) ) - $n,
) );
"""


def _main(html):
    m = re.search(r"<main\b.*?</main>", html, re.S)
    return m.group(0) if m else ""


def check(base, s):
    fails = []
    st, nav, _ = fetch(base + NAVESY)
    if st != 200:
        return ["%s: статус %s" % (NAVESY, st)]
    main = _main(nav)
    if not re.search(r"<h1[^>]*>Навесы для авто в Минске и по Беларуси</h1>", main):
        fails.append("навесы: нет h1 «Навесы для авто в Минске и по Беларуси»")
    for needle in ("Минской области", "по Беларуси", "<details", 'data-type="canopy" data-product="Навес для авто"',
                   'aria-pressed="true">Навес<', '<option value="Навес для авто" selected'):
        if needle not in main and needle not in nav:
            fails.append("навесы: нет «%s»" % needle)
    if "/nashi-raboty/" not in main or "kv-work__caption" not in main:
        fails.append("навесы: нет работ раздела навесов")

    _, home, _ = fetch(base + "/")
    products = re.search(r'<ul class="kv-products__list">.*?</ul>', home, re.S)
    if not products or base + NAVESY not in products.group(0):
        fails.append("меню «Продукция»: нет ссылки на навесы")
    m = _main(home)
    if 'href="%s%s"' % (base, NAVESY) not in m.split('id="raboty"')[0]:
        fails.append("главная: нет карточки навесов в «Что варим»")
    if 'data-type="canopy"' not in m:
        fails.append("главная: в калькуляторе нет вкладки «Навес»")
    brig = m.find("Бригадам и строителям")
    if brig < 0 or brig > m.find('id="zayavka"') or "безналичн" not in m[brig:brig + 2000]:
        fails.append("главная: нет блока «Бригадам и строителям» (опт, безнал) перед заявкой")
    st, sm, _ = fetch(base + "/wp-sitemap-posts-page-1.xml")
    if base + NAVESY not in sm:
        fails.append("sitemap: нет %s" % NAVESY)
    js = fetch(base + "/wp-content/themes/karkasvorot/assets/site.js")[1]
    if "длина" not in js:
        fails.append("site.js: кнопка навеса не пишет длину в заявку")

    for path in ("/karkas-otkatnyh-vorot/", "/karkas-raspashnyh-vorot/", "/kalitki/"):
        page = fetch(base + path)[1]
        if FRAME_Q not in page:
            fails.append("%s: нет вопроса «%s»" % (path, FRAME_Q))
        if not re.search(r"Вы делаете монтаж\?.*?Минской области, в том числе в Червенском районе", page, re.S):
            fails.append("%s: FAQ «Вы делаете монтаж?» без географии владельца" % path)

    # Owner's geography: montage = Minsk + region (incl. Cherven district), frames delivered across Belarus.
    for path in ("/", NAVESY, "/karkas-otkatnyh-vorot/", "/prochie-izdeliya/", "/kontakty/", "/nashi-raboty/"):
        page = fetch(base + path)[1]
        old = OLD_GEO.search(page)
        if old:
            fails.append("%s: прежняя география «%s»" % (path, old.group(0)))
        if "Червенск" not in page or "по всей Беларуси" not in page:
            fails.append("%s: нет «Червенский район» / «по всей Беларуси»" % path)
    area = [n["areaServed"] for n in map(json.loads, re.findall(r'<script type="application/ld\+json">(.*?)</script>', home, re.S)) if "areaServed" in n]
    if not area or {"@type": "AdministrativeArea", "name": "Минская область"} not in area[0]:
        fails.append("JSON-LD areaServed без «Минская область»: %r" % area)
    desc = re.search(r'<meta name="description" content="([^"]*)"', home)
    if not desc or OLD_GEO.search(desc.group(1)) or "Минск" not in desc.group(1):
        fails.append("главная: description %r" % (desc and desc.group(1)))
    _, misc, _ = fetch(base + "/prochie-izdeliya/")
    sec = re.search(r'id="navesy".*?</section>', misc, re.S)
    if not sec or base + NAVESY not in sec.group(0) or "Навесы и беседки на металлическом каркасе" in misc:
        fails.append("прочие изделия: раздел навесов не заменён ссылкой на страницу навесов")

    _, arch, _ = fetch(base + "/nashi-raboty/")
    if "usluga=karkas-otkatnyh-vorot" in arch:
        fails.append("работы: фильтр «Каркас откатных ворот» без работ виден")
    if "usluga=navesy-dlya-avto" not in arch:
        fails.append("работы: нет фильтра навесов")

    out = json.loads(run_php(base, STATE % ("array(" + ",".join("'%s'=>'%s'" % kv for kv in PHOTOS.items()) + ")")))
    for new, w in out["works"].items():
        stem = new.rsplit(".", 1)[0]
        if not w["file"].startswith(stem):
            fails.append("работа %s: картинка %r, нужна новая %s" % (PHOTOS[new], w["file"], new))
        if not w["src_alive"]:
            fails.append("работа %s: старое вложение удалено" % PHOTOS[new])
        if not w["alt"] or re.search(r"\d", w["alt"]):
            fails.append("%s: alt пуст или с цифрами: %r" % (new, w["alt"]))
    for old in ("slider1.jpg", "slider4.png"):
        new = [k for k, v in PHOTOS.items() if v == old][0]
        if out["works"][new]["service"] != "karkas-raspashnyh-vorot":
            fails.append("%s: не привязана к «Каркас распашных ворот»" % old)
    if out["works"]["naves-dlya-avto.jpg"]["service"] != "navesy-dlya-avto":
        fails.append("работа-навес не привязана к странице навесов")
    if out["mangal"][0] != 1 or out["mangal"][1] != "prochie-izdeliya" or not out["mangal"][2].startswith("mangal-na-podstavke"):
        fails.append("работа «Мангал на подставке»: %r" % out["mangal"])

    run_php(base, "wp_set_current_user( 1 ); kv_install();")
    again = json.loads(run_php(base, STATE % "array()"))
    if again["count"] != out["count"]:
        fails.append("повторная активация: вложений+работ+страниц %d → %d" % (out["count"], again["count"]))

    live = json.loads(run_php(base, LIVE))
    if not live["thumb"].startswith("vorota-shtaketnik-svetlye") or live["service"] != "karkas-raspashnyh-vorot":
        fails.append("обновление старой установки: работа slider1 %r" % live)
    if live["frame_q"] != 1:
        fails.append("обновление старой установки: вопрос про комплектующие %d раз" % live["frame_q"])
    if live["grew"] != 0:
        fails.append("обновление старой установки создало %d лишних записей" % live["grew"])
    return fails
