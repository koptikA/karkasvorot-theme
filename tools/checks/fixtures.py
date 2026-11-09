"""Stand media library = tools/fixtures/media.json (same files, alt and titles as the live site)."""
import json
import pathlib

from stand import run_php

MEDIA = pathlib.Path(__file__).resolve().parent.parent / "fixtures" / "media.json"

PHP = r"""
$out = array();
foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1 ) ) as $a ) {
	$out[ get_post_meta( $a->ID, '_wp_attached_file', true ) ] = array( get_post_meta( $a->ID, '_wp_attachment_image_alt', true ), $a->post_title );
}
echo wp_json_encode( $out );
"""


def check(base, s):
    want = {m["path"]: [m["alt"], m["title"]] for m in json.loads(MEDIA.read_text(encoding="utf-8"))}
    have = json.loads(run_php(base, PHP)) or {}
    fails = ["в медиатеке нет %s" % p for p in want if p not in have]
    fails += ["%s: alt/заголовок %r, на боевом %r" % (p, have[p], v) for p, v in want.items() if p in have and have[p] != v]
    return fails
