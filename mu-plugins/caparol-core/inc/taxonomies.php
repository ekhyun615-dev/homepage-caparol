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

/* 우선순위 5 — 포스트 타입(기본 10)보다 먼저 등록합니다.
   제품 주소가 /products/... 이고 카테고리 주소가 /products/category/... 라
   두 규칙이 겹칠 수 있는데, 분류를 먼저 등록하면 카테고리 규칙이 앞에 놓입니다. */
add_action( 'init', 'caparol_register_taxonomies', 5 );

function caparol_register_taxonomies() {

	/* 제품 특성 — 습윤마모 1등급 · 미네랄 · 라텍스 · 곰팡이 방지 · 차단
	 *
	 * 이건 "계층"이 아니라 "속성"입니다. 한 제품이 여러 개를 가질 수 있습니다.
	 * 그래서 메뉴에는 넣지 않고, 제품 목록 화면의 필터로만 씁니다.
	 * publicly_queryable = false : 이 분류만의 목록 주소는 만들지 않습니다.
	 *   (/products/category/xxx/?cp_feature=latex 형태로 카테고리 안에서 걸러집니다)
	 */
	register_taxonomy( 'product_feature', 'product', array(
		'labels'             => caparol_tax_labels( '제품 특성' ),
		'hierarchical'       => false,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_admin_column'  => true,
		'show_in_nav_menus'  => false,
		'show_in_rest'       => true,
	) );

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

/* ══════════════════════════════════════════════════════════
   2차 시드 — 하위 카테고리와 제품 특성

   1차 시드(caparol_terms_seeded)는 이미 실행됐으므로
   새로 추가되는 항목은 별도 옵션 키로 한 번만 더 실행합니다.

   ⚠️ 항목을 새로 추가할 때는 아래 옵션 키의 숫자를 반드시 올리세요.
      올리지 않으면 이미 실행된 사이트에서는 그냥 빠져나가
      새 항목이 만들어지지 않습니다.
      v3 → v4 : 외장용 특성 3종(NQG 자기세정 · 실리콘 수지 ·
                균열 가교·탄성) 추가 때문에 올렸습니다.
      만드는 부분이 전부 term_exists 로 감싸져 있어
      다시 돌아도 기존 항목이 중복되거나 덮어써지지 않습니다.
   ══════════════════════════════════════════════════════════ */
add_action( 'init', 'caparol_seed_terms_v2', 21 );

function caparol_seed_terms_v2() {

	if ( get_option( 'caparol_terms_seeded_v4' ) ) {
		return;
	}

	/* ── 하위 제품 카테고리 ──────────────────────────────
	   '부모슬러그' => array( '이름' => '슬러그', … )
	   부모가 없으면 건너뜁니다. */
	$children = array(
		'paint' => array(
			'인테리어 페인트' => 'paint-interior',
			'외장 페인트'     => 'paint-exterior',
		),
		'primer' => array(
			'프라이머'    => 'primer-base',
			'외벽 발수제' => 'water-repellent',
		),
		'plaster' => array(
			'외장 플라스터' => 'plaster-exterior',
			'내장 플라스터' => 'plaster-interior',
			'퍼티'          => 'putty',
		),
	);

	foreach ( $children as $parent_slug => $terms ) {

		$parent = get_term_by( 'slug', $parent_slug, 'product_cat' );
		if ( ! $parent || is_wp_error( $parent ) ) {
			continue;
		}

		foreach ( $terms as $name => $slug ) {
			if ( ! term_exists( $slug, 'product_cat' ) ) {
				wp_insert_term( $name, 'product_cat', array(
					'slug'   => $slug,
					'parent' => $parent->term_id,
				) );
			}
		}
	}

	/* ── 제품 특성 ───────────────────────────────────────
	   순서대로 만들어집니다. 필터 버튼도 이 순서로 나옵니다.
	   친환경은 설계사가 자재를 고를 때 가장 먼저 거르는 조건이라 맨 앞에 둡니다. */
	$features = array(
		'친환경'            => 'eco',
		'습윤마모 1등급'    => 'wear-class-1',
		'미네랄 · 실리케이트' => 'mineral',
		'라텍스'            => 'latex',
		'곰팡이 방지'       => 'anti-mould',
		'차단'              => 'isolating',

		/* 외장용 — 슬라이드의 제품 묶음을 그대로 옮겼습니다.
		   실내용 특성(습윤마모·곰팡이 방지)은 외벽에서 의미가 없어
		   따로 둡니다. 필터에서는 이어서 나옵니다. */
		'NQG 자기세정'      => 'self-cleaning',
		'실리콘 수지'       => 'silicone-resin',
		'균열 가교 · 탄성'  => 'crack-bridging',
	);

	foreach ( $features as $name => $slug ) {
		if ( ! term_exists( $slug, 'product_feature' ) ) {
			wp_insert_term( $name, 'product_feature', array( 'slug' => $slug ) );
		}
	}

	update_option( 'caparol_terms_seeded_v4', 1 );
}

/* ══════════════════════════════════════════════════════════
   주소 규칙 1회 자동 갱신

   분류·포스트 타입을 바꾸면 [설정 → 고유주소] 에서 저장을 눌러야
   주소가 살아납니다. 그 단계를 잊기 쉬워서 코드로 한 번 처리합니다.
   숫자를 올리면 다음 배포 때 다시 한 번 갱신됩니다.
   ══════════════════════════════════════════════════════════ */
add_action( 'init', 'caparol_maybe_flush_rules', 99 );

function caparol_maybe_flush_rules() {

	$version = '4';   // 구조를 바꿀 때마다 올립니다

	if ( get_option( 'caparol_rules_version' ) === $version ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'caparol_rules_version', $version );
}
