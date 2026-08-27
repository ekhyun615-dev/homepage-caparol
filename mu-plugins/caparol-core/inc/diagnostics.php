<?php
/**
 * 진단 화면 — 관리자 → 도구 → 카파롤 진단
 *
 * 카테고리 주소가 404 날 때 원인을 눈으로 확인하기 위한 화면입니다.
 * 관리자에게만 보이고, 방문자 화면에는 아무 영향이 없습니다.
 * 문제가 해결되면 이 파일은 지워도 됩니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_management_page(
		'카파롤 진단',
		'카파롤 진단',
		'manage_options',
		'caparol-diagnostics',
		'caparol_render_diagnostics'
	);
} );

/** 표 한 줄 */
function caparol_diag_row( $label, $value, $ok = null ) {
	$mark = '';
	if ( true === $ok )  { $mark = '<span style="color:#1a7f37;font-weight:700">● 정상</span> '; }
	if ( false === $ok ) { $mark = '<span style="color:#b91c1c;font-weight:700">● 문제</span> '; }
	printf(
		'<tr><th style="width:260px;text-align:left">%s</th><td>%s<code style="word-break:break-all">%s</code></td></tr>',
		esc_html( $label ),
		$mark,
		esc_html( (string) $value )
	);
}

function caparol_render_diagnostics() {

	echo '<div class="wrap"><h1>카파롤 진단</h1>';
	echo '<p>제품 카테고리 주소가 404 날 때 원인을 찾는 화면입니다. 이 표를 그대로 캡처해서 보내주세요.</p>';
	echo '<table class="widefat striped" style="max-width:1000px">';

	/* ── 1. 분류 등록 상태 ───────────────────────── */
	echo '<tr><td colspan="2" style="background:#f0f0f1"><strong>1. 분류(product_cat) 등록 상태</strong></td></tr>';

	$exists = taxonomy_exists( 'product_cat' );
	caparol_diag_row( 'taxonomy_exists', $exists ? 'true' : 'false', $exists );

	if ( $exists ) {
		$tax = get_taxonomy( 'product_cat' );
		caparol_diag_row( 'public', $tax->public ? 'true' : 'false', (bool) $tax->public );
		caparol_diag_row( 'publicly_queryable', $tax->publicly_queryable ? 'true' : 'false', (bool) $tax->publicly_queryable );
		caparol_diag_row( 'query_var', $tax->query_var ? $tax->query_var : '(없음)', (bool) $tax->query_var );
		caparol_diag_row( 'hierarchical', $tax->hierarchical ? 'true' : 'false' );
		caparol_diag_row( '연결된 포스트 타입', implode( ', ', (array) $tax->object_type ) );
		caparol_diag_row( 'rewrite slug', isset( $tax->rewrite['slug'] ) ? $tax->rewrite['slug'] : '(없음)' );
	}

	/* ── 2. 포스트 타입 ──────────────────────────── */
	echo '<tr><td colspan="2" style="background:#f0f0f1"><strong>2. 포스트 타입(product)</strong></td></tr>';

	$pt_exists = post_type_exists( 'product' );
	caparol_diag_row( 'post_type_exists', $pt_exists ? 'true' : 'false', $pt_exists );

	if ( $pt_exists ) {
		$pt = get_post_type_object( 'product' );
		caparol_diag_row( 'public', $pt->public ? 'true' : 'false', (bool) $pt->public );
		caparol_diag_row( 'publicly_queryable', $pt->publicly_queryable ? 'true' : 'false', (bool) $pt->publicly_queryable );
		caparol_diag_row( 'has_archive', is_string( $pt->has_archive ) ? $pt->has_archive : ( $pt->has_archive ? 'true' : 'false' ) );
		caparol_diag_row( 'rewrite slug', isset( $pt->rewrite['slug'] ) ? $pt->rewrite['slug'] : '(없음)' );
	}

	/* ── 3. 분류 항목 ────────────────────────────── */
	echo '<tr><td colspan="2" style="background:#f0f0f1"><strong>3. 인테리어 페인트 항목</strong></td></tr>';

	$term = get_term_by( 'slug', 'paint-interior', 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		caparol_diag_row( 'term_id / slug', $term->term_id . ' / ' . $term->slug, true );
		caparol_diag_row( 'count (워드프레스 집계)', $term->count );
		$link = get_term_link( $term );
		caparol_diag_row( 'get_term_link', is_wp_error( $link ) ? $link->get_error_message() : $link, ! is_wp_error( $link ) );
	} else {
		caparol_diag_row( '항목 찾기', '찾을 수 없음', false );
	}

	/* ── 4. 실제 질의 ────────────────────────────── */
	echo '<tr><td colspan="2" style="background:#f0f0f1"><strong>4. 실제로 제품이 잡히는지</strong></td></tr>';

	$q = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'tax_query'      => array( array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => 'paint-interior',
		) ),
	) );
	caparol_diag_row( '발행된 제품 수', $q->found_posts, $q->found_posts > 0 );
	if ( $q->have_posts() ) {
		$names = wp_list_pluck( $q->posts, 'post_title' );
		caparol_diag_row( '제품 목록', implode( ', ', $names ) );
	}
	wp_reset_postdata();

	/* ── 5. 주소 규칙 ────────────────────────────── */
	echo '<tr><td colspan="2" style="background:#f0f0f1"><strong>5. 주소 규칙</strong></td></tr>';

	$structure = get_option( 'permalink_structure' );
	caparol_diag_row( '고유주소 구조', $structure ? $structure : '기본(물음표 주소)', (bool) $structure );

	$rules = (array) get_option( 'rewrite_rules' );
	caparol_diag_row( '전체 규칙 수', count( $rules ), count( $rules ) > 0 );

	$found = array();
	foreach ( array_keys( $rules ) as $regex ) {
		if ( false !== strpos( $regex, 'products/category' ) ) {
			$found[] = $regex;
		}
	}
	caparol_diag_row( 'products/category 규칙', count( $found ) . '개', ! empty( $found ) );
	if ( $found ) {
		caparol_diag_row( '규칙 예시', $found[0] . '  →  ' . $rules[ $found[0] ] );
	}

	/* ── 6. 다른 플러그인 간섭 ───────────────────── */
	echo '<tr><td colspan="2" style="background:#f0f0f1"><strong>6. 참고</strong></td></tr>';
	caparol_diag_row( '활성 플러그인', implode( ', ', (array) get_option( 'active_plugins' ) ) );
	caparol_diag_row( '테마', get_stylesheet() . '  (부모: ' . get_template() . ')' );
	caparol_diag_row( '워드프레스 / PHP', get_bloginfo( 'version' ) . ' / ' . PHP_VERSION );

	echo '</table>';

	/* ── 바로 열어볼 주소 ────────────────────────── */
	$archive = get_post_type_archive_link( 'product' );
	echo '<h2 style="margin-top:24px">직접 눌러서 확인</h2><ul style="list-style:disc;margin-left:20px">';
	if ( $archive ) {
		printf( '<li><a href="%1$s" target="_blank">%1$s</a> — 제품 목록</li>', esc_url( $archive ) );
	}
	printf( '<li><a href="%1$s" target="_blank">%1$s</a> — 물음표 주소</li>',
		esc_url( home_url( '/?post_type=product&product_cat=paint-interior' ) ) );
	if ( $term && ! is_wp_error( $term ) && ! is_wp_error( get_term_link( $term ) ) ) {
		printf( '<li><a href="%1$s" target="_blank">%1$s</a> — 워드프레스가 만든 주소</li>',
			esc_url( get_term_link( $term ) ) );
	}
	echo '</ul></div>';
}
