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
