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
   제품 기술 데이터 · 주요 특징 출력

   ACF 무료판에는 반복 필드가 없습니다.
   그래서 텍스트영역 한 칸에 여러 줄을 받아 코드가 표·목록으로 그립니다.

   입력 예 (기술 데이터):
       광택도 : 무광
       소요량 : 0.15~0.20 ℓ/㎡
       희석률 : 물 5% 이내

   엑셀에서 두 칸을 복사해 붙여넣으면 탭으로 구분되는데, 그것도 그대로 인식합니다.
   ────────────────────────────────────────────── */

/**
 * 여러 줄 텍스트를 [항목, 값] 목록으로 변환합니다.
 *
 * 구분자는 탭(엑셀 복붙) → 콜론(: 또는 ：) 순서로 봅니다.
 * 값에 콜론이 들어가도(예: "혼합비 : 1:3") 첫 콜론에서만 나누므로 안전합니다.
 * 구분자가 아예 없는 줄은 버리지 않고 값 없는 줄로 남깁니다 —
 * 조용히 사라지면 입력한 사람이 빠진 걸 눈치채지 못합니다.
 *
 * @return array 각 항목은 array('label' => string, 'value' => string|null)
 */
function caparol_parse_lines( $raw ) {
	$rows = array();

	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return $rows;
	}

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}

		if ( false !== strpos( $line, "\t" ) ) {
			$parts = explode( "\t", $line, 2 );
			$rows[] = array(
				'label' => trim( $parts[0] ),
				'value' => trim( $parts[1] ),
			);
		} elseif ( preg_match( '/^(.+?)\s*[:：]\s*(.*)$/u', $line, $m ) ) {
			$rows[] = array(
				'label' => trim( $m[1] ),
				'value' => trim( $m[2] ),
			);
		} else {
			$rows[] = array(
				'label' => $line,
				'value' => null,
			);
		}
	}

	return $rows;
}

/**
 * 기술 데이터 표를 출력합니다. Elementor 단축코드 위젯에서 [caparol_specs] 로 쓰세요.
 */
function caparol_specs_table( $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$rows = caparol_parse_lines( get_field( 'specs', $post_id ?: get_the_ID() ) );
	if ( ! $rows ) {
		return '';
	}

	$html = '<div class="caparol-specs-wrap"><table class="caparol-specs"><tbody>';
	foreach ( $rows as $row ) {
		if ( null === $row['value'] ) {
			// 구분자를 빠뜨린 줄 — 한 칸으로 넓게 보여 눈에 띄게 합니다
			$html .= sprintf(
				'<tr><td colspan="2">%s</td></tr>',
				esc_html( $row['label'] )
			);
			continue;
		}
		$html .= sprintf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			esc_html( $row['label'] ),
			esc_html( $row['value'] )
		);
	}
	$html .= '</tbody></table></div>';

	return $html;
}
add_shortcode( 'caparol_specs', function () {
	return caparol_specs_table();
} );

/**
 * 주요 특징 목록을 출력합니다. [caparol_features] 로 쓰세요.
 *
 * 한 줄에 하나씩 입력받습니다. 줄 앞의 -, ·, • 는 알아서 떼어냅니다.
 */
function caparol_features_list( $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$raw = get_field( 'features', $post_id ?: get_the_ID() );
	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return '';
	}

	$html = '<ul class="caparol-features">';
	$empty = true;

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( preg_replace( '/^[\-·•*]\s*/u', '', trim( $line ) ) );
		if ( '' === $line ) {
			continue;
		}
		$html  .= '<li>' . esc_html( $line ) . '</li>';
		$empty  = false;
	}

	$html .= '</ul>';

	return $empty ? '' : $html;
}
add_shortcode( 'caparol_features', function () {
	return caparol_features_list();
} );

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

/* ──────────────────────────────────────────────
   한글 슬러그 경고

   제목을 한글로 쓰면 주소(슬러그)도 한글이 됩니다.
   화면에는 "/products/암피볼린/" 으로 보이지만 실제 주소는
   "/products/%EC%95%94%ED%94%BC%EB%B3%BC%EB%A6%B0/" 입니다.
   카카오톡·문자로 공유할 때 링크가 잘리고, 문서에 적으면 한 줄을 다 잡아먹습니다.

   나중에 고치면 이미 퍼진 링크가 전부 죽으므로, 등록 직후에 알려줍니다.
   ────────────────────────────────────────────── */

/** 슬러그가 영문·숫자·하이픈이 아닌 문자를 포함하는지 */
function caparol_slug_needs_fix( $slug ) {
	if ( '' === $slug ) {
		return false;
	}
	// 워드프레스는 한글 슬러그를 퍼센트 인코딩해서 저장합니다
	return (bool) preg_match( '/%[0-9a-f]{2}/i', $slug )
		|| (bool) preg_match( '/[^\x20-\x7e]/', $slug );
}

/* 편집 화면 상단에 경고 */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}
	if ( ! in_array( $screen->post_type, array( 'product', 'reference', 'color', 'document' ), true ) ) {
		return;
	}

	$post = get_post();
	if ( ! $post || ! caparol_slug_needs_fix( $post->post_name ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>주소(슬러그)가 한글입니다.</strong> ' .
		'오른쪽 <em>슬러그</em> 항목을 영문 소문자와 하이픈으로 바꿔주세요. ' .
		'예: <code>amphibolin</code>, <code>yangpyeong-house</code><br>' .
		'한글 주소는 카카오톡·문자로 공유할 때 링크가 깨집니다. ' .
		'공개 후에 바꾸면 이미 퍼진 링크가 모두 죽으니 <strong>지금 고치는 것이 가장 쌉니다.</strong></p></div>'
	);
} );

/* 목록 화면에 슬러그 열 추가 — 잘못된 것들을 한눈에 */
foreach ( array( 'product', 'reference', 'color', 'document' ) as $caparol_pt ) {

	add_filter( "manage_{$caparol_pt}_posts_columns", function ( $columns ) {
		$columns['caparol_slug'] = '주소(슬러그)';
		return $columns;
	} );

	add_action( "manage_{$caparol_pt}_posts_custom_column", function ( $column, $post_id ) {
		if ( 'caparol_slug' !== $column ) {
			return;
		}
		$slug = get_post_field( 'post_name', $post_id );

		if ( '' === $slug ) {
			echo '<span style="color:#999">—</span>';
			return;
		}
		if ( caparol_slug_needs_fix( $slug ) ) {
			printf(
				'<strong style="color:#d63638">%s</strong><br><small>영문으로 변경 필요</small>',
				esc_html( urldecode( $slug ) )
			);
			return;
		}
		echo '<code>' . esc_html( $slug ) . '</code>';
	}, 10, 2 );
}
