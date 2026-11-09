"""Owner revision: descriptive images and work names; no public amounts."""
import json
import re
from stand import fetch, run_php

def check(base, s):
    fails = []
    for path in ('/', '/karkas-otkatnyh-vorot/', '/karkas-raspashnyh-vorot/', '/kalitki/', '/prochie-izdeliya/', '/nashi-raboty/'):
        status, html, _ = fetch(base + path)
        if status != 200:
            fails.append(path + ': HTTP ' + str(status))
        if re.search(r'\bslider\d+\b', html, re.I):
            fails.append(path + ': technical filename/name exposed')
        if re.search(r'\d[\d\s.,–-]*\s*(?:BYN|руб)', html, re.I):
            fails.append(path + ': public numeric price')
    result = json.loads(run_php(base, """
    $before = array_map(function($p) { return array($p->ID, $p->post_title, $p->post_name); }, get_posts(array('post_type'=>'work','numberposts'=>-1)));
    kv_refresh_imported_work_names();
    $after = array_map(function($p) { return array($p->ID, $p->post_title, $p->post_name); }, get_posts(array('post_type'=>'work','numberposts'=>-1)));
    $a = kv_attachment_by_file('slider1.jpg');
    echo wp_json_encode(array('has_legacy'=>(bool) get_posts(array('post_type'=>'work','meta_key'=>'_wp_old_slug','meta_value'=>'slider1','numberposts'=>1)), 'stable'=>$before === $after, 'original'=>wp_get_attachment_url($a), 'derivative'=>kv_catalog_image($a)));
    """))
    if not result['stable']:
        fails.append('name migration is not idempotent')
    if not result['derivative'] or 'slider' in result['derivative']['src']:
        fails.append('missing descriptive derivative')
    for url in (result['original'], result['derivative'].get('src', '')):
        if not url or fetch(url)[0] != 200:
            fails.append('image unavailable: ' + str(url))
    code, _, destination = fetch(base + '/nashi-raboty/slider1/')
    if result['has_legacy'] and (code != 200 or '/slider1/' in destination):
        fails.append('legacy work URL does not resolve to renamed work')
    return fails
