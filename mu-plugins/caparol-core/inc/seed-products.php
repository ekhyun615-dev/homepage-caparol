<?php
/**
 * 인테리어 페인트 9종 — 등록 틀 만들기 (한 번만 실행)
 *
 * 제목·영문 슬러그·카테고리·특성까지 채운 **임시글**을 만듭니다.
 * 직원분은 내용만 채우고 [공개]를 누르면 됩니다.
 *
 * ⚠️ 임시글(draft)입니다. 사이트에는 나오지 않습니다.
 *    필요 없으면 관리자 → 제품 에서 휴지통으로 보내면 됩니다.
 *
 * 슬러그를 코드로 정해두는 이유:
 *   제목이 영문이라도 워드프레스가 만드는 슬러그가 어긋날 때가 있고,
 *   한글 제목을 쓰면 주소가 깨집니다. 여기서 못 박아 둡니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'caparol_seed_interior_paints', 22 );

function caparol_seed_interior_paints() {

	if ( get_option( 'caparol_products_seeded_interior' ) ) {
		return;
	}

	// 카테고리가 아직 없으면 다음 요청에서 다시 시도합니다
	$cat = get_term_by( 'slug', 'paint-interior', 'product_cat' );
	if ( ! $cat || is_wp_error( $cat ) ) {
		return;
	}

	/* 제목 => array( 슬러그, 특성 슬러그, 한 줄 요약 ) */
	$items = array(
		'Indeko-Plus' => array( 'indeko-plus', 'wear-class-1',
			'습윤마모 1등급 무광 내부 도료. 은폐력과 내구성이 높아 반복 청소가 필요한 공간에 적합합니다.' ),

		'CapaSilan' => array( 'capasilan', 'wear-class-1',
			'실리콘 강화 내부 도료. 미세 균열을 덮어주고 매끈한 무광 마감을 만듭니다.' ),

		'PremiumClean' => array( 'premiumclean', 'wear-class-1',
			'오염 제거가 쉬운 내부 도료. 학교·병원 등 손이 자주 닿는 벽면에 사용합니다.' ),

		'Sylitol Bio-Innenfarbe' => array( 'sylitol-bio-innenfarbe', 'mineral',
			'실리케이트계 미네랄 도료. 통기성이 높아 습기가 있는 실내에 적합합니다.' ),

		'Histolith Innenkalk' => array( 'histolith-innenkalk', 'mineral',
			'석회계 내부 마감재. 문화재·리모델링 현장의 미네랄 바탕면에 사용합니다.' ),

		'Latex Gloss 60' => array( 'latex-gloss-60', 'latex',
			'유광 라텍스 도료. 내수성과 세척성이 필요한 벽면에 사용합니다.' ),

		'SeidenLatex' => array( 'seidenlatex', 'latex',
			'반광 라텍스 도료. 내구성을 유지하면서 광택을 낮춘 마감입니다.' ),

		'Indeko-W' => array( 'indeko-w', 'anti-mould',
			'곰팡이 방지 내부 도료. 욕실·주방 등 결로가 생기기 쉬운 공간에 사용합니다.' ),

		'Aqua-inn № 1' => array( 'aqua-inn-no1', 'isolating',
			'차단 도료. 니코틴·수분 얼룩 등이 배어 나오는 것을 막고 도장합니다.' ),
	);

	foreach ( $items as $title => $data ) {

		list( $slug, $feature, $summary ) = $data;

		// 이미 같은 슬러그의 제품이 있으면 건너뜁니다
		$exists = get_page_by_path( $slug, OBJECT, 'product' );
		if ( $exists ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'product',
			'post_status' => 'draft',          // 임시글 — 사이트에 안 나옵니다
			'post_title'  => $title,
			'post_name'   => $slug,
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		wp_set_object_terms( $post_id, array( (int) $cat->term_id ), 'product_cat' );

		if ( $feature && term_exists( $feature, 'product_feature' ) ) {
			wp_set_object_terms( $post_id, array( $feature ), 'product_feature' );
		}

		// 요약은 ACF summary 필드와 같은 자리에 넣어둡니다 (수정은 관리자 화면에서)
		update_post_meta( $post_id, 'summary', $summary );
	}

	update_option( 'caparol_products_seeded_interior', 1 );
}

/* ══════════════════════════════════════════════════════════
   특성 보강 — 이미 만들어진 제품에 특성을 덧붙입니다

   '친환경' 특성을 나중에 추가했기 때문에, 카탈로그에서 근거가 확인된
   제품에만 한 번 붙여줍니다. 기존 특성은 지우지 않고 더합니다.
   ══════════════════════════════════════════════════════════ */
add_action( 'init', 'caparol_seed_product_features_eco', 23 );

function caparol_seed_product_features_eco() {

	if ( get_option( 'caparol_products_eco_tagged' ) ) {
		return;
	}
	if ( ! term_exists( 'eco', 'product_feature' ) ) {
		return;   // 특성이 아직 안 만들어졌으면 다음 요청에서 다시 시도
	}

	/* 카탈로그에 친환경 근거가 명시된 제품만
	   Indeko-Plus — E.L.F. Plus, 방부제 무첨가, CO₂ 절감, 알러지 인증 */
	$slugs = array( 'indeko-plus' );

	foreach ( $slugs as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'product' );
		if ( ! $post ) {
			continue;
		}
		// append = true : 기존 특성(습윤마모 1등급)을 지우지 않고 더합니다
		wp_set_object_terms( $post->ID, array( 'eco' ), 'product_feature', true );
	}

	update_option( 'caparol_products_eco_tagged', 1 );
}

/* ══════════════════════════════════════════════════════════
   특성 보강 2차 — 카탈로그 5종(CapaSilan · PremiumClean ·
   Sylitol Bio-Innenfarbe · Histolith Innenkalk · Latex Gloss 60)
   자료를 받고 근거가 확인된 것만 덧붙입니다.

   기존 특성은 지우지 않고 더합니다(append). 붙인 게 마음에 안 들면
   관리자 → 제품 편집 화면에서 체크만 해제하면 됩니다.
   이 함수는 한 번만 돌기 때문에 해제한 게 다시 붙지 않습니다.

   Histolith Innenkalk 는 일부러 뺐습니다.
   카탈로그에 E.L.F. 라벨도, 저배출·무용제 표기도 없습니다.
   석회 도료라 친환경으로 보이지만 근거가 적혀 있지 않습니다.
   ══════════════════════════════════════════════════════════ */
add_action( 'init', 'caparol_seed_product_features_v2', 24 );

function caparol_seed_product_features_v2() {

	if ( get_option( 'caparol_products_features_v2' ) ) {
		return;
	}

	/* 슬러그 => 덧붙일 특성 슬러그들 */
	$map = array(
		// 저배출 · 무용제 · E.L.F.
		'capasilan'              => array( 'eco' ),

		// 친환경적이고 저취 · 물로 희석 가능 · E.L.F.
		'premiumclean'           => array( 'eco' ),

		// 보존제 무첨가 · 무용제 · 가소제 무첨가 · TUV Nord 알레르기 인증 · E.L.F. plus
		'sylitol-bio-innenfarbe' => array( 'eco' ),

		// 저배출 · 무용제 · 가소제 무첨가 · E.L.F.  +  습윤 마모 R-등급 1
		'latex-gloss-60'         => array( 'eco', 'wear-class-1' ),
	);

	foreach ( $map as $slug => $features ) {

		$post = get_page_by_path( $slug, OBJECT, 'product' );
		if ( ! $post ) {
			continue;
		}

		foreach ( $features as $feature ) {
			if ( ! term_exists( $feature, 'product_feature' ) ) {
				return;   // 특성이 아직 안 만들어졌으면 다음 요청에서 통째로 다시 시도
			}
			// append = true : 기존 특성을 지우지 않고 더합니다
			wp_set_object_terms( $post->ID, array( $feature ), 'product_feature', true );
		}
	}

	update_option( 'caparol_products_features_v2', 1 );
}

/* ══════════════════════════════════════════════════════════
   특성 보강 3차 — 마지막 3종
   (SeidenLatex · Indeko-W · Aqua-inn 1) 자료를 받고 추가합니다.

   Indeko-W 는 일부러 뺐습니다.
   E.L.F. 라벨과 AgBB 시험 근거는 있지만, 이 제품은 곰팡이·세균으로부터
   도막을 보호하는 보존 성분(살생물 성분)을 함유합니다.
   방부제 무첨가를 내세우는 Indeko-Plus · Sylitol 과 같은 '친환경' 으로
   묶으면 친환경으로 걸러 찾아온 고객이 이 제품을 보게 됩니다.
   ══════════════════════════════════════════════════════════ */
add_action( 'init', 'caparol_seed_product_features_v3', 25 );

function caparol_seed_product_features_v3() {

	if ( get_option( 'caparol_products_features_v3' ) ) {
		return;
	}

	/* 슬러그 => 덧붙일 특성 슬러그들 */
	$map = array(
		// 저배출 · 무용제 · 가소제 무첨가 · E.L.F.  +  습윤 마모 1 등급
		'seidenlatex'  => array( 'eco', 'wear-class-1' ),

		// E.L.F. 라벨  +  습윤 마모 R 등급 1
		'aqua-inn-no1' => array( 'eco', 'wear-class-1' ),
	);

	foreach ( $map as $slug => $features ) {

		$post = get_page_by_path( $slug, OBJECT, 'product' );
		if ( ! $post ) {
			continue;
		}

		foreach ( $features as $feature ) {
			if ( ! term_exists( $feature, 'product_feature' ) ) {
				return;   // 특성이 아직 안 만들어졌으면 다음 요청에서 통째로 다시 시도
			}
			// append = true : 기존 특성을 지우지 않고 더합니다
			wp_set_object_terms( $post->ID, array( $feature ), 'product_feature', true );
		}
	}

	update_option( 'caparol_products_features_v3', 1 );
}
