"""Pages, texts, works and the installer (task 02), through HTTP + run_php on the stand."""
import json
import re

from stand import PHP_ERR, fetch, run_php

SERVICES = {  # slug -> strings the page must show (price, material from the spec table)
    "/karkas-otkatnyh-vorot/": ["1000", "BYN", "60×40×2", "3×1,5"],
    "/karkas-raspashnyh-vorot/": ["500", "BYN", "60×40×2"],
    "/kalitki/": ["270", "BYN", "60×40×2"],
    "/prochie-izdeliya/": ["300", "BYN", "20×20×2", "40×40×2", "цена по запросу",
                           'id="mebel"', 'id="obuvnicy"', 'id="zabory"', 'id="navesy"'],
}
PAGES = ["/", "/nashi-raboty/", "/kontakty/", "/politika-konfidencialnosti/"] + list(SERVICES)
DISCLAIMER = "цена может измениться"
INVENTED = re.compile(r"гаранти|опыт|стаж|\d+\s*(лет|год|дн|недел|объект|клиент)", re.I)

COUNT = r"""
$n = function () {
	return count( get_posts( array( 'post_type' => array( 'page', 'work' ), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) )
		. '/' . count( wp_get_nav_menu_items( get_nav_menu_locations()['primary'] ?? 0 ) ?: array() );
};
$before = $n();
wp_set_current_user( 1 );
kv_install();
$old = array();
foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
	if ( '' === $p->post_title || 0 === strpos( $p->post_title, 'Слайд' ) ) {
		$old[] = array( $p->ID, $p->post_status );
	}
}
echo wp_json_encode( array( 'before' => $before, 'after' => $n(), 'old' => $old, 'front' => get_option( 'show_on_front' ) . ':' . get_post_field( 'post_name', (int) get_option( 'page_on_front' ) ), 'report' => is_array( get_option( 'kv_install_report' ) ) ) );
"""

FOREIGN = r"""
wp_set_current_user( 1 );
$p = get_page_by_path( 'kalitki' );
$tpl = get_post_meta( $p->ID, '_wp_page_template', true );
$old = $p->post_content;
delete_post_meta( $p->ID, '_wp_page_template' );
$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, array( 'post_content' => 'kv чужой контент' ), array( 'ID' => $p->ID ) );
clean_post_cache( $p->ID );
$g = get_page_by_path( 'glavnaya' );
wp_update_post( array( 'ID' => $g->ID, 'post_status' => 'draft' ) );
update_option( 'show_on_front', 'posts' );
kv_install();
$rep = get_option( 'kv_install_report' );
$out = array(
	'content'   => get_post_field( 'post_content', $p->ID ),
	'count'     => count( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => 'kalitki', 'numberposts' => -1, 'fields' => 'ids' ) ) ),
	'attention' => isset( $rep['attention'] ) ? (array) $rep['attention'] : array(),
	'front'     => get_option( 'show_on_front' ),
);
$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, array( 'post_content' => $old ), array( 'ID' => $p->ID ) );
clean_post_cache( $p->ID );
update_post_meta( $p->ID, '_wp_page_template', $tpl );
wp_update_post( array( 'ID' => $g->ID, 'post_status' => 'publish' ) );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $g->ID );
echo wp_json_encode( $out );
"""



INSERT_FAILURE = r"""
wp_set_current_user( 1 );
$page = get_page_by_path( 'kalitki' );
$front = get_option( 'show_on_front' );
$locations = get_theme_mod( 'nav_menu_locations' );
$old_report = get_option( 'kv_install_report' );
$block = function ( $empty, $post ) { return isset( $post['post_name'] ) && 'kalitki' === $post['post_name'] ? true : $empty; };
try {
    wp_update_post( array( 'ID' => $page->ID, 'post_name' => 'kv-held-kalitki' ) );
    update_option( 'show_on_front', 'posts' );
    set_theme_mod( 'nav_menu_locations', array() );
    add_filter( 'wp_insert_post_empty_content', $block, 10, 2 );
    kv_install();
    $report = get_option( 'kv_install_report' );
    $out = array( 'front' => get_option( 'show_on_front' ), 'menu' => get_nav_menu_locations(), 'attention' => $report['attention'] );
} finally {
    remove_filter( 'wp_insert_post_empty_content', $block, 10 );
    wp_update_post( array( 'ID' => $page->ID, 'post_name' => 'kalitki' ) );
    update_option( 'show_on_front', $front );
    set_theme_mod( 'nav_menu_locations', $locations );
    update_option( 'kv_install_report', $old_report );
}
echo wp_json_encode( $out );
"""


def _main(html):
    m = re.search(r"<main\b.*?</main>", html, re.S)
    return m.group(0) if m else ""


def _text(html):
    return re.sub(r"\s+", " ", re.sub(r"<[^>]+>", " ", html))


def check(base, s):
    fails = []
    html = {}
    # Include every published work, not only the fixed landing pages.
    _, works, _ = fetch(base + "/nashi-raboty/")
    extra = re.findall(r'kv-work__caption"><a href="' + re.escape(base) + r'([^"?]+)"', works)
    for path in dict.fromkeys(PAGES + extra):
        st, body, _ = fetch(base + path)
        html[path] = body
        if st != 200:
            fails.append("%s: статус %s" % (path, st))
            continue
        if PHP_ERR.search(body):
            fails.append("%s: PHP-ошибка: %s" % (path, PHP_ERR.search(body).group(0)[:160]))
        h1 = len(re.findall(r"<h1\b", body))
        if h1 != 1:
            fails.append("%s: h1 — %d шт., нужен ровно один" % (path, h1))
        invented = INVENTED.search(_text(_main(body)))
        if invented:
            fails.append("%s: похоже на выдуманный факт: «%s»" % (path, invented.group(0)))
        for img in re.findall(r"<img\b[^>]*>", _main(body)):
            if not re.search(r'\balt="[^"]+"', img):
                fails.append("%s: <img> без alt: %s" % (path, img[:120]))
    for path, needles in SERVICES.items():
        main = _main(html[path])
        text = _text(main)
        for n in needles + [DISCLAIMER]:
            if n.lower() not in text.lower() and n not in main:
                fails.append("%s: нет «%s» в <main>" % (path, n))
        if "<details" not in html[path]:
            fails.append("%s: нет вопросов-ответов <details>" % path)
    for path in SERVICES:
        if 'href="%s%s"' % (base, path) not in _main(html["/"]):
            fails.append("главная: нет ссылки на %s" % path)
    if DISCLAIMER not in html["/"]:
        fails.append("главная: нет оговорки о цене")
    works = set(re.findall(r'href="%s/nashi-raboty/([^/"?]+)/"' % re.escape(base), _main(html["/nashi-raboty/"])))
    if len(works) < 10:
        fails.append("«Наши работы»: %d работ, нужно ≥ 10" % len(works))
    st, kal, _ = fetch(base + "/nashi-raboty/?usluga=kalitki")
    if st != 200 or "Скоро добавим новые работы" not in kal:
        fails.append("«Наши работы» с фильтром без работ: нет «Скоро добавим новые работы»")
    pol = _text(_main(html["/politika-konfidencialnosti/"]))
    for n in ("99-З", "Яндекс", "cookie", "отозвать"):
        if n not in pol:
            fails.append("политика: нет «%s»" % n)
    for n in ("tel:+375447726823", "viber://", "t.me/", "Минская область"):
        if n not in _main(html["/kontakty/"]):
            fails.append("контакты: нет %s" % n)

    out = json.loads(run_php(base, COUNT))
    if out["before"] != out["after"]:
        fails.append("повторный kv_install(): страницы+работы/пункты меню %s → %s" % (out["before"], out["after"]))
    if not out["after"].endswith("/6"):
        fails.append("меню primary: %s пунктов, нужно 6" % out["after"].split("/")[1])
    if out["front"] != "page:glavnaya":
        fails.append("главная не статическая glavnaya: %s" % out["front"])
    if not out["report"]:
        fails.append("нет опции kv_install_report")
    if len(out["old"]) != 4:
        fails.append("старых слайдов %d, ожидалось 4" % len(out["old"]))
    for pid, status in out["old"]:
        if status != "draft":
            fails.append("слайд %s в статусе %s, нужен draft" % (pid, status))
        if fetch(base + "/?p=%d" % pid)[0] != 404:
            fails.append("слайд /?p=%d не 404" % pid)
    if fetch(base + "/sample-page/")[0] != 404:
        fails.append("/sample-page/ не 404")

    failed = json.loads(run_php(base, INSERT_FAILURE))
    if failed["front"] != "posts" or failed["menu"]:
        fails.append("ошибка wp_insert_post изменила главную или меню: %r" % failed)
    if not any("kalitki" in item for item in failed["attention"]):
        fails.append("ошибка wp_insert_post не попала в отчёт")

    out = json.loads(run_php(base, FOREIGN))
    if out["content"] != "kv чужой контент" or out["count"] != 1:
        fails.append("установщик тронул чужую страницу kalitki: %r" % out)
    if not any("kalitki" in a for a in out["attention"]):
        fails.append("чужая страница kalitki не попала в kv_install_report['attention']: %r" % out["attention"])
    if out["front"] != "posts":
        fails.append("установщик сделал главной неопубликованную glavnaya")

    # Price edited in the page field shows up on the home card and on the service page.
    run_php(base, "update_post_meta( get_page_by_path( 'kalitki' )->ID, '_kv_price_from', '271' );")
    try:
        for path in ("/", "/kalitki/"):
            if "271" not in _text(_main(fetch(base + path)[1])):
                fails.append("%s: новая цена из поля «Цена от» не видна" % path)
    finally:
        run_php(base, "update_post_meta( get_page_by_path( 'kalitki' )->ID, '_kv_price_from', '270' );")
    return fails
