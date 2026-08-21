<?php
/**
 * Caparol Korea 자식 테마
 *
 * 여기에는 "보이는 것"만 넣습니다.
 * 콘텐츠 구조(제품·시공사례 등록)는 mu-plugins/caparol-core 가 담당합니다.
 * 테마를 바꿔도 데이터가 남아야 하기 때문입니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ──────────────────────────────────────────────
   스타일 로드
   ────────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'caparol-child',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'generate-style' ),                       // 부모 테마 뒤에 로드
		wp_get_theme()->get( 'Version' )
	);
}, 20 );

/* ──────────────────────────────────────────────
   ACF 필드 정의를 저장소에 남기기 (Local JSON)

   이걸 켜두면 관리자 화면에서 필드를 수정할 때마다
   acf-json/ 폴더에 JSON이 자동 저장됩니다.
   → 필드 정의가 DB에만 있지 않고 Git에 남습니다.
   → 로컬에서 만든 필드를 서버에 그대로 옮길 수 있습니다.
   ────────────────────────────────────────────── */
add_filter( 'acf/settings/save_json', function () {
	return get_stylesheet_directory() . '/acf-json';
} );

add_filter( 'acf/settings/load_json', function ( $paths ) {
	unset( $paths[0] );
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
} );

/* ──────────────────────────────────────────────
   모바일 하단 고정 바 (전화 · 카카오 · 상담신청)

   ⚠️ 아래 연락처는 임시값입니다. 실제 값으로 교체하세요.
   ────────────────────────────────────────────── */
add_action( 'wp_footer', function () {
	$tel   = '0212345678';                    // TODO: 실제 대표번호
	$kakao = 'https://pf.kakao.com/_XXXXXX';  // TODO: 실제 카카오채널 URL

	printf(
		'<nav class="caparol-floating" aria-label="빠른 문의">
			<a href="tel:%1$s">☎ 전화문의</a>
			<a href="%2$s" target="_blank" rel="noopener">💬 카카오톡</a>
			<a href="%3$s" class="is-primary">📝 상담신청</a>
		</nav>',
		esc_attr( $tel ),
		esc_url( $kakao ),
		esc_url( home_url( '/contact/' ) )
	);
} );

/* ──────────────────────────────────────────────
   제품 기술 데이터 표 출력

   ACF 반복 필드 `specs`(항목명 label + 값 value)를 표로 렌더링합니다.
   템플릿에서 caparol_specs_table() 로 호출하세요.
   ────────────────────────────────────────────── */
function caparol_specs_table( $post_id = null ) {
	if ( ! function_exists( 'have_rows' ) ) {
		return; // ACF 미설치
	}
	$post_id = $post_id ?: get_the_ID();

	if ( ! have_rows( 'specs', $post_id ) ) {
		return;
	}

	echo '<div class="caparol-specs-wrap"><table class="caparol-specs"><tbody>';
	while ( have_rows( 'specs', $post_id ) ) {
		the_row();
		printf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			esc_html( get_sub_field( 'label' ) ),
			esc_html( get_sub_field( 'value' ) )
		);
	}
	echo '</tbody></table></div>';
}

/* ──────────────────────────────────────────────
   관리 편의
   ────────────────────────────────────────────── */

/* 편집자(Editor)도 메뉴를 수정할 수 있게 — 직원이 직접 운영하는 사이트이므로 */
add_action( 'admin_init', function () {
	$editor = get_role( 'editor' );
	if ( $editor && ! $editor->has_cap( 'edit_theme_options' ) ) {
		$editor->add_cap( 'edit_theme_options' );
	}
} );

/* 관리자 화면에서 안 쓰는 위젯 정리 */
add_action( 'wp_dashboard_setup', function () {
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );   // 워드프레스 뉴스
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
} );

/* 검색 결과에서 색상·기술자료는 제외 (제품·시공사례만 노출) */
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$query->set( 'post_type', array( 'post', 'page', 'product', 'reference' ) );
	}
} );
