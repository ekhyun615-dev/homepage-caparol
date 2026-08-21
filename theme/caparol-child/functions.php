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
		array( 'astra-theme-css' ),                      // Astra 스타일 뒤에 로드
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
   Astra 조정

   Elementor로 만든 템플릿이 적용되는 곳에서는 Astra가 그리는
   기본 제목·여백이 중복으로 나옵니다. 커스터마이저에서 끌 수도 있지만,
   설정이 DB에만 남으므로 코드로 고정합니다.
   ────────────────────────────────────────────── */

/* 제품·시공사례·색상·기술자료는 Elementor 템플릿이 제목을 그립니다 */
add_filter( 'astra_the_title_enabled', function ( $enabled ) {
	if ( is_singular( array( 'product', 'reference', 'color', 'document' ) ) ) {
		return false;
	}
	return $enabled;
} );

/* Elementor가 콘텐츠 폭을 직접 제어하도록 — 좌우 여백 중복 방지 */
add_filter( 'astra_page_layout', function ( $layout ) {
	if ( is_singular( array( 'product', 'reference', 'color', 'document' ) ) ) {
		return 'no-sidebar';
	}
	return $layout;
} );

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

/* ──────────────────────────────────────────────
   기술자료 목록에 유효기간 표시

   시험성적서는 유효기간이 있습니다. 만료된 성적서가 사이트에 걸려 있으면
   설계사에게 신뢰를 잃습니다. 관리자 목록에서 바로 보이게 합니다.
   ────────────────────────────────────────────── */

add_filter( 'manage_document_posts_columns', function ( $columns ) {
	$columns['valid_until'] = '유효기간';
	return $columns;
} );

add_action( 'manage_document_posts_custom_column', function ( $column, $post_id ) {
	if ( 'valid_until' !== $column ) {
		return;
	}
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$raw = get_field( 'valid_until', $post_id, false ); // Ymd 원본으로 받기
	if ( ! $raw ) {
		echo '<span style="color:#999">—</span>';
		return;
	}

	$expires = strtotime( $raw );
	$today   = current_time( 'timestamp' );
	$days    = floor( ( $expires - $today ) / DAY_IN_SECONDS );
	$date    = date_i18n( 'Y-m-d', $expires );

	if ( $days < 0 ) {
		printf( '<strong style="color:#d63638">%s · 만료됨</strong>', esc_html( $date ) );
	} elseif ( $days <= 60 ) {
		printf( '<strong style="color:#b26200">%s · %d일 남음</strong>', esc_html( $date ), (int) $days );
	} else {
		printf( '<span>%s</span>', esc_html( $date ) );
	}
}, 10, 2 );
