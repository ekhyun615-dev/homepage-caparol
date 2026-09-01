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

/* ══════════════════════════════════════════════════════════
   앞화면 질의 들여다보기

   주소 뒤에 ?cp_debug=1 을 붙이면 화면 아래에 회색 상자가 붙습니다.
   워드프레스가 그 주소를 어떻게 해석했는지 그대로 보여줍니다.
   관리자로 로그인했을 때만 보입니다.

   예) https://caparol.mycafe24.com/products/category/paint-interior/?cp_debug=1
   ══════════════════════════════════════════════════════════ */
add_action( 'wp_footer', 'caparol_front_debug', 999 );

function caparol_front_debug() {

	if ( ! isset( $_GET['cp_debug'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wp, $wp_query;

	$obj  = get_queried_object();
	$name = '(없음)';
	if ( $obj instanceof WP_Term ) {
		$name = 'WP_Term — ' . $obj->taxonomy . ' / ' . $obj->slug;
	} elseif ( $obj instanceof WP_Post_Type ) {
		$name = 'WP_Post_Type — ' . $obj->name;
	} elseif ( $obj instanceof WP_Post ) {
		$name = 'WP_Post — ' . $obj->post_type . ' / ' . $obj->post_name;
	} elseif ( $obj ) {
		$name = get_class( $obj );
	}

	$lines = array(
		'요청 주소'       => isset( $wp->request ) ? $wp->request : '(없음)',
		'걸린 주소 규칙'   => isset( $wp->matched_rule ) ? $wp->matched_rule : '(없음 — 규칙에 안 걸림)',
		'해석된 질의'     => isset( $wp->matched_query ) ? $wp->matched_query : '(없음)',
		'query_vars'     => wp_json_encode( $wp->query_vars, JSON_UNESCAPED_UNICODE ),
		'is_404'         => $wp_query->is_404 ? 'TRUE  ← 404 처리됨' : 'false',
		'is_tax'         => $wp_query->is_tax ? 'true' : 'false',
		'is_post_type_archive' => $wp_query->is_post_type_archive ? 'true' : 'false',
		'queried_object' => $name,
		'found_posts'    => $wp_query->found_posts,
		'post_count'     => $wp_query->post_count,
		/* 목록이 몇 페이지로 갈리는지 — "한 페이지에 다 안 나와요" 의 원인 확인용.
		   제품 목록에서 posts_per_page 가 24 가 아니면
		   서버의 functions.php 가 저장소보다 오래된 것입니다. */
		'posts_per_page' => $wp_query->get( 'posts_per_page' ),
		'max_num_pages'  => $wp_query->max_num_pages,
		'실행된 SQL'      => $wp_query->request,
	);

	echo '<div style="position:relative;z-index:99999;margin:0;padding:20px;background:#111;color:#0f0;'
		. 'font:12px/1.6 ui-monospace,Menlo,Consolas,monospace;overflow-x:auto">';
	echo '<strong style="color:#fff">카파롤 진단 — 이 주소를 워드프레스가 어떻게 해석했는지 (관리자만 보임)</strong><br><br>';
	foreach ( $lines as $k => $v ) {
		printf(
			'<span style="color:#888">%-22s</span> %s<br>',
			esc_html( $k ),
			esc_html( (string) $v )
		);
	}
	echo '</div>';
}
