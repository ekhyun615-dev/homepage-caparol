<?php
/**
 * 주소(고유주소) 진단
 *
 * 카테고리 주소가 404 날 때 원인을 눈으로 확인하기 위한 화면입니다.
 * 관리자 → 제품 → 제품 카테고리 화면 맨 위에만 표시되며, 방문자에게는 보이지 않습니다.
 *
 * 문제가 해결되면 이 파일은 지워도 됩니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_notices', 'caparol_rewrite_diagnostics' );

function caparol_rewrite_diagnostics() {

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-product_cat' !== $screen->id ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wp_rewrite;

	$rows = array();

	// 1. 고유주소 구조
	$structure = get_option( 'permalink_structure' );
	$rows['고유주소 구조'] = $structure ? $structure : '⚠️ 기본(물음표 주소) — 설정 → 고유주소 에서 바꿔야 합니다';

	// 2. 카테고리 주소 규칙이 실제로 있는지
	$rules = (array) get_option( 'rewrite_rules' );
	$found = array();
	foreach ( array_keys( $rules ) as $regex ) {
		if ( false !== strpos( $regex, 'products/category' ) ) {
			$found[] = $regex;
		}
	}
	$rows['products/category 규칙 수'] = $found
		? count( $found ) . '개 — 정상'
		: '⚠️ 0개 — 규칙이 만들어지지 않았습니다';

	if ( $found ) {
		$rows['규칙 예시'] = esc_html( $found[0] ) . '  →  ' . esc_html( $rules[ $found[0] ] );
	}

	// 3. 워드프레스가 만드는 실제 주소
	$term = get_term_by( 'slug', 'paint-interior', 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		$rows['인테리어 페인트 주소'] = is_wp_error( $link ) ? '⚠️ ' . $link->get_error_message() : $link;
	} else {
		$rows['인테리어 페인트'] = '⚠️ 분류를 찾을 수 없습니다';
	}

	// 4. 제품 목록 주소
	$archive = get_post_type_archive_link( 'product' );
	$rows['제품 목록 주소'] = $archive ? $archive : '⚠️ 없음';

	// 5. 전체 규칙 개수
	$rows['전체 주소 규칙 수'] = count( $rules ) . '개';

	echo '<div class="notice notice-info"><p><strong>주소 진단 (관리자만 보입니다)</strong></p><table class="widefat striped" style="max-width:900px;margin-bottom:12px">';
	foreach ( $rows as $k => $v ) {
		printf(
			'<tr><th style="width:220px">%s</th><td><code style="word-break:break-all">%s</code></td></tr>',
			esc_html( $k ),
			esc_html( (string) $v )
		);
	}
	echo '</table></div>';
}
