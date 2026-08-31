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
 * ⚠️ 요약 문장은 아직 카탈로그를 받기 전에 쓴 초안입니다.
 *    제품 묶음(슬라이드의 5개 그룹)과 제품명에서 확실한 것만 적었고,
 *    성능 수치나 인증은 넣지 않았습니다.
 *    카탈로그가 오면 docs/products/*-input.txt 로 덮어써 주세요.
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
			'NQG 기술을 적용한 외벽 마감용 도료입니다. 단열 마감면을 포함한 외벽에 사용합니다.' ),

		'Sylitol NQG' => array( 'sylitol-nqg', array( 'self-cleaning', 'mineral' ),
			'실리케이트 계열에 NQG 기술을 더한 외벽 도료입니다. 미네랄 바탕면의 통기성을 유지합니다.' ),

		'Sylitol NQG-W' => array( 'sylitol-nqg-w', array( 'self-cleaning', 'mineral' ),
			'Sylitol NQG 계열의 외벽 도료입니다. W 사양의 차이는 카탈로그 확인이 필요합니다.' ),

		/* ── 실리콘 / SilaCryl 외장페인트 ─────────────── */
		'AmphiSilan' => array( 'amphisilan', array( 'silicone-resin' ),
			'실리콘 수지 계열 외벽 도료입니다. 발수성과 통기성을 함께 갖춘 마감입니다.' ),

		'Muresko' => array( 'muresko', array( 'silicone-resin' ),
			'SilaCryl 기반 외벽 도료입니다. 일반적인 외벽 마감에 폭넓게 사용합니다.' ),

		/* ── 미네랄 외장페인트 ────────────────────────── */
		'Sylitol Fassadenfarbe' => array( 'sylitol-fassadenfarbe', array( 'mineral' ),
			'실리케이트 외벽 도료입니다. 미네랄 바탕면에 결합하는 방식으로 마감합니다.' ),

		'Sylitol Finish 130' => array( 'sylitol-finish-130', array( 'mineral' ),
			'실리케이트 계열 외벽 마감재입니다. Sylitol 시스템의 마감 도료로 사용합니다.' ),

		/* ── 분산형 페인트 ────────────────────────────── */
		'Amphibolin' => array( 'amphibolin', array(),
			'바탕면을 가리지 않고 쓰는 범용 분산형 도료입니다. 내부와 외부에 모두 사용합니다.' ),

		/* ── 균열 보수 · 탄성 도장 시스템 ─────────────── */
		'PermaSilan' => array( 'permasilan', array( 'crack-bridging', 'silicone-resin' ),
			'균열 가교 성능을 갖춘 탄성 외벽 도료입니다. 미세 균열이 있는 외벽에 사용합니다.' ),

		'FibroSil' => array( 'fibrosil', array( 'crack-bridging' ),
			'섬유로 보강한 중도재입니다. 균열 보수 도장 시스템의 중간층으로 사용합니다.' ),

		'Cap-elast Riss-Spachtel' => array( 'cap-elast-riss-spachtel', array( 'crack-bridging' ),
			'균열 보수용 퍼티입니다. Cap-elast 균열 보수 시스템의 구성품입니다.' ),
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
