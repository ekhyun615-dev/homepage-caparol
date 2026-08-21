<?php
/**
 * 분류(택소노미) 등록
 *
 * 분류를 코드로 등록하되, 실제 항목(외단열시스템·외부도료 등)은
 * 관리자 화면에서 직원이 직접 추가·수정할 수 있습니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'caparol_register_taxonomies' );

function caparol_register_taxonomies() {

	/* 제품 카테고리 — 외단열시스템 / 외부도료 / 내부도료 / 미장·스타코 / 장식마감 */
	register_taxonomy( 'product_cat', 'product', array(
		'labels'            => caparol_tax_labels( '제품 카테고리' ),
		'hierarchical'      => true,      // 상하위 구조 사용
		'public'            => true,
		'rewrite'           => array( 'slug' => 'products/category', 'with_front' => false ),
		'show_admin_column' => true,
		'show_in_rest'      => true,
	) );

	/* 시공사례 — 건물 용도 (단독주택 / 상가 / 공공 / 산업) */
	register_taxonomy( 'reference_type', 'reference', array(
		'labels'            => caparol_tax_labels( '건물 용도' ),
		'hierarchical'      => true,
		'public'            => true,
		'rewrite'           => array( 'slug' => 'references/type', 'with_front' => false ),
		'show_admin_column' => true,
		'show_in_rest'      => true,
	) );

	/* 시공사례 — 지역 */
	register_taxonomy( 'reference_region', 'reference', array(
		'labels'            => caparol_tax_labels( '지역' ),
		'hierarchical'      => true,
		'public'            => true,
		'rewrite'           => array( 'slug' => 'references/region', 'with_front' => false ),
		'show_admin_column' => true,
		'show_in_rest'      => true,
	) );

	/* 색상 계열 — 화이트 / 그레이 / 베이지 / 레드 … */
	register_taxonomy( 'color_family', 'color', array(
		'labels'            => caparol_tax_labels( '색상 계열' ),
		'hierarchical'      => true,
		'public'            => true,
		'rewrite'           => array( 'slug' => 'colors/family', 'with_front' => false ),
		'show_admin_column' => true,
		'show_in_rest'      => true,
	) );

	/* 기술자료 구분 — 카탈로그 / TDS / 시공지침 / 시험성적서 */
	register_taxonomy( 'document_type', 'document', array(
		'labels'            => caparol_tax_labels( '자료 구분' ),
		'hierarchical'      => true,
		'public'            => true,
		'rewrite'           => array( 'slug' => 'downloads/type', 'with_front' => false ),
		'show_admin_column' => true,
		'show_in_rest'      => true,
	) );
}

function caparol_tax_labels( $name ) {
	return array(
		'name'          => $name,
		'singular_name' => $name,
		'search_items'  => $name . ' 검색',
		'all_items'     => $name . ' 전체',
		'edit_item'     => $name . ' 편집',
		'update_item'   => $name . ' 업데이트',
		'add_new_item'  => $name . ' 추가',
		'new_item_name' => '새 ' . $name,
		'menu_name'     => $name,
	);
}

/**
 * ──────────────────────────────────────────────────────────
 * 분류 항목 최초 1회 생성
 *
 * 제품 카테고리 7종의 슬러그가 URL이 되므로, 손으로 만들다 오타가 나면
 * 나중에 고칠 때 주소가 바뀌어 SEO가 깨집니다. 코드로 한 번에 만듭니다.
 *
 * 한 번 실행되면 옵션 플래그가 남아 다시 만들지 않습니다.
 * → 직원이 항목을 지우거나 이름을 바꿔도 되살아나지 않습니다.
 * 다시 만들고 싶으면 옵션 'caparol_terms_seeded' 를 삭제하세요.
 * ────────────────────────────────────────────────────────── */
add_action( 'init', 'caparol_seed_terms', 20 );

function caparol_seed_terms() {

	if ( get_option( 'caparol_terms_seeded' ) ) {
		return;
	}

	$seed = array(

		// 제품 카테고리 — 슬러그는 URL이 되므로 변경 시 리다이렉트 필요
		'product_cat' => array(
			'프라이머'              => 'primer',
			'페인트'                => 'paint',
			'플라스터 / 퍼티'        => 'plaster',
			'유리직물벽지'          => 'capaver',
			'장식 인테리어'         => 'decor',
			'외단열시스템'          => 'eifs',
			'Meldorfer 벽돌마감'    => 'meldorfer',
		),

		// 시공사례 — 국내 / 해외 (하위 지역은 관리자 화면에서 추가)
		'reference_region' => array(
			'국내' => 'korea',
			'해외' => 'global',
		),

		// 건물 용도
		'reference_type' => array(
			'단독주택' => 'house',
			'상업시설' => 'commercial',
			'공공건축' => 'public',
			'산업시설' => 'industrial',
		),

		// 기술자료 구분 — 시험성적서를 맨 앞에 두려고 순서대로 생성
		'document_type' => array(
			'시험성적서 · 인증서' => 'certificate',
			'카탈로그'            => 'catalog',
			'기술자료 (TDS)'      => 'tds',
			'시공지침'            => 'guide',
		),
	);

	foreach ( $seed as $taxonomy => $terms ) {
		foreach ( $terms as $name => $slug ) {
			if ( ! term_exists( $slug, $taxonomy ) ) {
				wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
			}
		}
	}

	update_option( 'caparol_terms_seeded', 1 );
}
