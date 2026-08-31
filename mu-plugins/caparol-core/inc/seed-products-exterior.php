<?php
/**
 * 외장용 페인트 11종 — 등록 틀 만들기 (한 번만 실행)
 *
 * 제목·영문 슬러그·카테고리·특성까지 채운 **임시글**을 만듭니다.
 * 직원분은 내용만 채우고 [공개]를 누르면 됩니다.
 *
 * ⚠️ 임시글(draft)입니다. 사이트에는 나오지 않습니다.
 *    필요 없으면 관리자 → 제품 에서 휴지통으로 보내면 됩니다.
 *
 * 요약 문장은 Caparol 카탈로그 슬라이드의 제품 소개 문장을 옮긴 것입니다.
 * 나머지 항목(용도·특징·기술 데이터·시공 참고)은
 * docs/products/*-input.txt 와 *-body-blocks.txt 에 있습니다.
 *
 * 슬러그를 코드로 정해두는 이유:
 *   제목이 영문이라도 워드프레스가 만드는 슬러그가 어긋날 때가 있고,
 *   한글 제목을 쓰면 주소가 깨집니다. 여기서 못 박아 둡니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'caparol_seed_exterior_paints', 26 );

function caparol_seed_exterior_paints() {

	if ( get_option( 'caparol_products_seeded_exterior' ) ) {
		return;
	}

	// 카테고리가 아직 없으면 다음 요청에서 다시 시도합니다
	$cat = get_term_by( 'slug', 'paint-exterior', 'product_cat' );
	if ( ! $cat || is_wp_error( $cat ) ) {
		return;
	}

	/* 제목 => array( 슬러그, 특성 슬러그 배열, 한 줄 요약 )

	   특성은 슬라이드의 제품 묶음을 그대로 옮겼습니다.
	   Sylitol 계열은 실리케이트라 mineral 을 함께 붙입니다. */
	$items = array(

		/* ── NQG 외장페인트 ───────────────────────────── */
		'ThermoSan NQG' => array( 'thermosan-nqg', array( 'self-cleaning' ),
			'깨끗하고 빠르게 건조되는 파사드를 위한, 나노-쿼츠 격자 기술(NQG)이 통합된 최고급 외장용 페인트입니다.' ),

		'Sylitol NQG' => array( 'sylitol-nqg', array( 'self-cleaning', 'mineral' ),
			'깨끗한 외벽을 위한 하이테크 외장용 페인트입니다. 고품질 알칼리 물유리, 솔-실리케이트, 나노-쿼츠 격자 구조를 통합한 하이브리드 바인더의 독특한 조합으로 이루어져 있습니다.' ),

		'Sylitol NQG-W' => array( 'sylitol-nqg-w', array( 'self-cleaning', 'mineral' ),
			'Sylitol NQG 와 같은 하이브리드 바인더에 살생물성 도막 보호 기능을 더한 하이테크 외장용 페인트입니다.' ),

		/* ── 실리콘 / SilaCryl 외장페인트 ─────────────── */
		'AmphiSilan' => array( 'amphisilan', array( 'silicone-resin' ),
			'미네랄 무광 외장용 페인트입니다. 특수 실리콘수지 바인더 조합과 모세관 발수성 표면을 통해 외벽을 깨끗하게 유지하고 빠르게 건조되도록 합니다.' ),

		'Muresko' => array( 'muresko', array( 'silicone-resin' ),
			'SilaCryl® 기반의 범용 실란화 순수 아크릴 외장용 페인트입니다. 기능성 실리콘 매트릭스를 갖추어 수증기 투과성과 매우 우수한 발수성을 함께 지니며, 폭넓은 색상 범위를 제공합니다.' ),

		/* ── 미네랄 외장페인트 ────────────────────────── */
		'Sylitol Fassadenfarbe' => array( 'sylitol-fassadenfarbe', array( 'mineral' ),
			'DIN 18363, 2.4.1항에 따른 실리케이트 기반의 미네랄 무광 외장용 페인트입니다.' ),

		'Sylitol Finish 130' => array( 'sylitol-finish-130', array( 'mineral' ),
			'실리케이트 기반의 내후성 외장 및 균일화 도장용 마감재입니다.' ),

		/* ── 분산형 페인트 ────────────────────────────── */
		'Amphibolin' => array( 'amphibolin', array(),
			'주택 내 · 외부의 다양한 용도에 사용할 수 있는 순수 아크릴 기반의 범용 페인트입니다.' ),

		/* ── 균열 보수 · 탄성 도장 시스템 ─────────────── */
		'PermaSilan' => array( 'permasilan', array( 'crack-bridging', 'silicone-resin' ),
			'플라스터 표면 균열의 균열 추종 및 가교를 위한 저온 탄성형 수증기 투과성 외장용 페인트입니다.' ),

		'FibroSil' => array( 'fibrosil', array( 'crack-bridging' ),
			'섬유 보강형 균열용 프라이머입니다. 균열을 메우는 하도 및 중도 도장재로 사용합니다.' ),

		'Cap-elast Riss-Spachtel' => array( 'cap-elast-riss-spachtel', array( 'crack-bridging' ),
			'균열이 발생한 플라스터 외벽과 콘크리트 표면의 보수를 위한 최고급 플라스토-탄성 도장 시스템입니다. Riss-Spachtel 은 균열을 충전하는 플라스토-탄성 퍼티입니다.' ),
	);

	foreach ( $items as $title => $data ) {

		list( $slug, $features, $summary ) = $data;

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

		// 아직 안 만들어진 특성은 건너뜁니다
		$valid = array();
		foreach ( $features as $feature ) {
			if ( term_exists( $feature, 'product_feature' ) ) {
				$valid[] = $feature;
			}
		}
		if ( $valid ) {
			wp_set_object_terms( $post_id, $valid, 'product_feature' );
		}

		// 요약은 ACF summary 필드와 같은 자리에 넣어둡니다 (수정은 관리자 화면에서)
		update_post_meta( $post_id, 'summary', $summary );
	}

	update_option( 'caparol_products_seeded_exterior', 1 );
}
