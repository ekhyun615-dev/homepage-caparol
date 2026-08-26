<?php
/**
 * 시공사례 분류별 목록
 * 실제 내용은 inc/reference-archive.php 에 있습니다 (지역·용도가 같은 화면이므로).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
caparol_render_reference_taxonomy();
get_footer();
