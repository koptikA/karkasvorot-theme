"""Settings, content model and module stubs, through the stand (HTTP + run_php)."""
import json
import re

from stand import fetch, run_php

KEYS = {
    "kv_phone": "+375 44 772-68-23",
    "kv_viber": "viber://chat?number=%2B375447726823",
    "kv_telegram": "Maksimus05061981",
    "kv_whatsapp": "375447726823",
    "kv_email": "m.krasnolobov@gmail.com", "kv_hours": "", "kv_org_name": "Индивидуальный предприниматель Краснолобов Максим Валерьевич", "kv_unp": "191469362", "kv_address": "",
    "kv_registration": "Зарегистрирован 12.08.2011, учёт в инспекции МНС по Партизанскому району г. Минска",
    "kv_metrika_id": "", "kv_yandex_verification": "", "kv_google_verification": "",
}
SECTIONS = {"kv_contacts": "Контакты", "kv_requisites": "Реквизиты", "kv_analytics": "Аналитика"}

PHP = r"""
require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
wp_set_current_user( 1 );
$m = new WP_Customize_Manager();
do_action( 'customize_register', $m );
$out = array( 'sections' => array(), 'settings' => array() );
foreach ( array( 'kv_contacts', 'kv_requisites', 'kv_analytics' ) as $id ) {
	$s = $m->get_section( $id );
	$out['sections'][ $id ] = $s ? $s->title : null;
}
foreach ( $m->settings() as $id => $s ) {
	if ( 0 === strpos( $id, 'kv_' ) ) { $out['settings'][ $id ] = $s->default; }
}
$w = get_post_type_object( 'work' );
$l = get_post_type_object( 'lead' );
$out['work'] = $w ? array( 'public' => $w->public, 'archive' => $w->has_archive ) : null;
$out['lead'] = $l ? array( 'public' => $l->public, 'rest' => $l->show_in_rest, 'edit' => $l->cap->edit_posts, 'read' => $l->cap->read_post ) : null;
$out['phone_href'] = function_exists( 'kv_phone_href' ) ? kv_phone_href() : null;
$out['stubs'] = array(
	'install'     => function_exists( 'kv_install' ) ? array_keys( kv_install() ) : null,
	'breadcrumbs' => function_exists( 'kv_breadcrumbs' ) ? kv_breadcrumbs() : null,
	'seo'         => function_exists( 'kv_seo_plugin_active' ) ? kv_seo_plugin_active() : null,
	'form'        => function_exists( 'kv_order_form' ),
);
if ( function_exists( 'kv_service_price' ) ) {
	$page = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'kv test service', 'post_status' => 'publish', 'page_template' => 'page-service.php' ) );
	$_POST = wp_slash( array(
		'kv_service_nonce' => wp_create_nonce( 'kv_service_meta' ),
		'kv_price_from' => '1000', 'kv_includes' => "труба 60×40×2 мм\n\nгрунтовка", 'kv_illustration' => '41',
		'kv_term' => '', 'kv_warranty' => '',
		'kv_faq' => "Вопрос \"1\"?\nОтвет 1\n\nВопрос 2?\nОтвет 2",
		'kv_prices' => array( 'mebel' => '300', 'obuvnicy' => '300', 'zabory' => '', 'navesy' => '' ),
	) );
	wp_update_post( array( 'ID' => $page, 'post_title' => 'kv test service' ) );
	$work = wp_insert_post( array( 'post_type' => 'work', 'post_title' => 'kv test work', 'post_status' => 'publish' ) );
	$_POST = wp_slash( array( 'kv_work_nonce' => wp_create_nonce( 'kv_work_meta' ), 'kv_service' => (string) $page, 'kv_section' => 'mebel', 'kv_city' => 'Минск' ) );
	wp_update_post( array( 'ID' => $work, 'post_title' => 'kv test work' ) );
	$out['price'] = kv_service_price( $page );
	$out['illustration'] = get_post_meta( $page, '_kv_illustration', true );
	$out['faq'] = json_decode( get_post_meta( $page, '_kv_faq', true ), true );
	$out['prices'] = json_decode( get_post_meta( $page, '_kv_prices', true ), true );
	$out['in_services'] = in_array( $page, wp_list_pluck( kv_service_pages(), 'ID' ), true );
	$out['city'] = get_post_meta( $work, '_kv_city', true );
	$out['works_by_service'] = wp_list_pluck( kv_works( $page ), 'ID' ) === array( $work );
	$out['works_by_section'] = wp_list_pluck( kv_works( $page, 12, 'mebel' ), 'ID' ) === array( $work );
	$out['works_other'] = kv_works( $page, 12, 'zabory' ) === array();
	$_POST = wp_slash( array( 'kv_service_nonce' => 'bad', 'kv_price_from' => '999', 'kv_includes' => 'x', 'kv_faq' => '' ) );
	wp_update_post( array( 'ID' => $page, 'post_title' => 'kv test service' ) );
	$_POST = wp_slash( array( 'kv_work_nonce' => 'bad', 'kv_service' => '0', 'kv_section' => '', 'kv_city' => 'Брест' ) );
	wp_update_post( array( 'ID' => $work, 'post_title' => 'kv test work' ) );
	$_POST = array();
	$out['bad_nonce'] = array( get_post_meta( $page, '_kv_price_from', true ), get_post_meta( $work, '_kv_city', true ) );
	wp_delete_post( $work, true );
	wp_delete_post( $page, true );
}
$k = get_page_by_path( 'kalitki' );
$q = $GLOBALS['wp_query'];
$q->query( array( 'page_id' => $k->ID ) );
$GLOBALS['wp_the_query'] = $q;
$GLOBALS['post'] = $q->post;
$c = kv_breadcrumbs();
$out['crumbs'] = array( count( $c ), $c ? $c[0]['url'] === home_url( '/' ) : false, $c ? end( $c )['url'] === get_permalink( $k ) : false );
echo wp_json_encode( $out );
"""
TAGGED = r"""
wp_set_current_user( 1 );
$p = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'kv заголовок <em>kvem</em>', 'post_content' => 'x' ) );
echo $p . ' ' . get_permalink( $p );
"""


def check(base, s):
    fails = []
    out = json.loads(run_php(base, PHP))
    for sid, title in SECTIONS.items():
        if out["sections"].get(sid) != title:
            fails.append("Customizer: секция %s «%s» не найдена (%r)" % (sid, title, out["sections"].get(sid)))
    if out["settings"] != KEYS:
        fails.append("Customizer: ключи/умолчания %r, ожидались %r" % (out["settings"], KEYS))
    if out["phone_href"] != "tel:+375447726823":
        fails.append("kv_phone_href() = %r" % out["phone_href"])
    if out["work"] != {"public": True, "archive": "nashi-raboty"}:
        fails.append("тип work: %r" % out["work"])
    if out["lead"] != {"public": False, "rest": False, "edit": "manage_options", "read": "manage_options"}:
        fails.append("тип lead: %r" % out["lead"])
    if out["stubs"] != {"install": ["created", "skipped", "drafted"], "breadcrumbs": [], "seo": False, "form": True}:
        fails.append("заглушки модулей: %r" % out["stubs"])
    if out.get("price") != {"from": "1000", "includes": ["труба 60×40×2 мм", "грунтовка"]}:
        fails.append("метабокс услуги / kv_service_price: %r" % out.get("price"))
    if out.get("illustration") != "41":
        fails.append("_kv_illustration: %r" % out.get("illustration"))
    if out.get("faq") != [{"q": 'Вопрос "1"?', "a": "Ответ 1"}, {"q": "Вопрос 2?", "a": "Ответ 2"}]:
        fails.append("_kv_faq: %r" % out.get("faq"))
    if out.get("prices") != {"mebel": "300", "obuvnicy": "300", "zabory": "", "navesy": ""}:
        fails.append("_kv_prices: %r" % out.get("prices"))
    for k in ("in_services", "works_by_service", "works_by_section", "works_other"):
        if out.get(k) is not True:
            fails.append("content-model: %s не выполнено" % k)
    if out.get("city") != "Минск":
        fails.append("метабокс работы: _kv_city = %r" % out.get("city"))

    if out.get("bad_nonce") != ["1000", "Минск"]:
        fails.append("сохранение метабокса с неверным nonce изменило мета: %r" % out.get("bad_nonce"))
    if out.get("crumbs") != [2, True, True]:
        fails.append("kv_breadcrumbs() на странице «Калитки»: %r, ожидались главная → страница" % out.get("crumbs"))
    pid, url = run_php(base, TAGGED).split()
    try:
        for where, u in (("запись", url), ("карточка в поиске", base + "/?s=kvem")):
            m = re.search(r"<main[ >].*?</main>", fetch(u)[1], re.S)  # <head> meta rightly escape tags
            body = m.group(0) if m else ""
            if "<em>kvem</em>" not in body or "&lt;em&gt;" in body:
                fails.append("%s: заголовок с тегами выведен буквально" % where)
    finally:
        run_php(base, "wp_delete_post(%d, true);" % int(pid))

    st, _, _ = fetch(base + "/?rest_route=/wp/v2/lead")
    if st != 404:
        fails.append("REST /wp/v2/lead: статус %s, ожидался 404" % st)
    hint = "Заполните: Внешний вид → Настроить → Реквизиты"
    _, anon, _ = fetch(base + "/")
    if hint in anon:
        fails.append("плашка «Заполните…» видна посетителю")
    if s is None:
        fails.append("нет admin-сессии (admin/password)")
    elif hint not in fetch(base + "/", s)[1]:
        fails.append("админу не видна плашка «Заполните…» на пустых реквизитах")
    return fails
