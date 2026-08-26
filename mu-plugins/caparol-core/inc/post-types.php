<?php
/**
 * 커스텀 포스트 타입 등록
 *
 * 제품을 "페이지"로 만들면 100개가 넘는 순간 관리가 불가능해집니다.
 * 포스트 타입 + 분류(택소노미)로 만들어야 목록·필터·메뉴가 자동으로 생성됩니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'caparol_register_post_types' );

function caparol_register_post_types() {

	/* ── 제품 ────────────────────────────────────────────── */
	register_post_type( 'product', array(
		'labels' => caparol_pt_labels( '제품', '제품' ),
		'public'              => true,
		'has_archive'         => 'products',        // /products
		'rewrite'             => array( 'slug' => 'products', 'with_front' => false ),
		'menu_icon'           => 'dashicons-products',
		'menu_position'       => 20,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
		'show_in_rest'        => true,               // 블록 에디터 사용
		'hierarchical'        => false,
	) );

	/* ── 시공 사례 ───────────────────────────────────────── */
	register_post_type( 'reference', array(
		'labels' => caparol_pt_labels( '시공사례', '시공사례' ),
		'public'              => true,
		'has_archive'         => 'references',
		'rewrite'             => array( 'slug' => 'references', 'with_front' => false ),
		'menu_icon'           => 'dashicons-building',
		'menu_position'       => 21,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'show_in_rest'        => true,
	) );

	/* ── 색상 ────────────────────────────────────────────── */
	// 도료 브랜드의 핵심 콘텐츠. 색상번호 · HEX · 계열은 ACF 필드로 붙입니다.
	register_post_type( 'color', array(
		'labels' => caparol_pt_labels( '색상', '색상' ),
		'public'              => true,
		'has_archive'         => 'colors',
		'rewrite'             => array( 'slug' => 'colors', 'with_front' => false ),
		'menu_icon'           => 'dashicons-art',
		'menu_position'       => 22,
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest'        => true,
	) );

	/* ── 기술자료 ────────────────────────────────────────── */
	// 카탈로그 · TDS · 시공지침 · 시험성적서. 파일은 ACF file 필드로 첨부합니다.
	register_post_type( 'document', array(
		'labels' => caparol_pt_labels( '기술자료', '기술자료' ),
		'public'              => true,
		'has_archive'         => 'downloads',
		'rewrite'             => array( 'slug' => 'downloads', 'with_front' => false ),
		'menu_icon'           => 'dashicons-media-document',
		'menu_position'       => 23,
		'supports'            => array( 'title', 'excerpt', 'page-attributes' ),
		'show_in_rest'        => true,
	) );

	/* ── 문의 접수 ───────────────────────────────────────── */
	// 홈페이지 문의 폼으로 들어온 내용이 여기 쌓입니다.
	// public => false : 이 글은 사이트에 절대 노출되지 않습니다. 관리자만 봅니다.
	// 메일이 스팸함에 빠지거나 발송이 실패해도 문의가 사라지지 않게 하려는 장치입니다.
	register_post_type( 'inquiry', array(
		'labels' => caparol_pt_labels( '문의', '문의 접수' ),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 24,
		'supports'            => array( 'title', 'editor' ),
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),  // 관리자도 손으로 추가하지 않습니다
		'map_meta_cap'        => true,
	) );
}

/**
 * 관리자 화면 라벨을 한 번에 만들어주는 헬퍼.
 * 포스트 타입마다 12줄씩 반복해서 쓰지 않기 위한 것입니다.
 */
function caparol_pt_labels( $singular, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'add_new'            => '새로 추가',
		'add_new_item'       => $singular . ' 추가',
		'edit_item'          => $singular . ' 편집',
		'new_item'           => '새 ' . $singular,
		'view_item'          => $singular . ' 보기',
		'search_items'       => $plural . ' 검색',
		'not_found'          => $plural . '이(가) 없습니다',
		'not_found_in_trash' => '휴지통에 ' . $plural . '이(가) 없습니다',
		'all_items'          => $plural . ' 전체',
		'menu_name'          => $plural,
	);
}
