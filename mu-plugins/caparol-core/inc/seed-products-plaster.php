<?php
/**
 * 외장 플라스터 — 등록 틀 만들기 (한 번만 실행)
 *
 * 슬라이드 「Capatect 외장플라스터」의 제품을 임시글로 만듭니다.
 * 카테고리는 페인트가 아니라 플라스터/퍼티 > 외장 플라스터 입니다.
 *
 * ⚠️ 임시글(draft)입니다. 사이트에는 나오지 않습니다.
 *    카탈로그는 아직 받기 전이라 요약은 슬라이드의 묶음에서
 *    확실한 것만 적었습니다. 성능 수치는 넣지 않았습니다.
 *    카탈로그가 오면 docs/products/*-input.txt 로 채워 주세요.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'caparol_seed_exterior_plaster', 27 );

function caparol_seed_exterior_plaster() {

	if ( get_option( 'caparol_products_seeded_plaster' ) ) {
		return;
	}

	// 카테고리가 아직 없으면 다음 요청에서 다시 시도합니다
	$cat = get_term_by( 'slug', 'plaster-exterior', 'product_cat' );
	if ( ! $cat || is_wp_error( $cat ) ) {
		return;
	}

	/* 제목 => array( 슬러그, 특성 슬러그 배열, 한 줄 요약 )
	   특성은 슬라이드의 묶음을 그대로 옮겼습니다. */
	$items = array(

		/* ── NQG 외장플라스터 ─────────────────────────── */
		'ThermoSan Fassadenputz NQG' => array( 'thermosan-fassadenputz-nqg', array( 'self-cleaning' ),
			'NQG 기술을 적용한 외벽 마감용 플라스터입니다. 오염을 방지해 외벽을 오래 깨끗하게 유지합니다.' ),

		/* ── 실리콘 외장플라스터 ──────────────────────── */
		'AmphiSilan Fassadenputz' => array( 'amphisilan-fassadenputz', array( 'silicone-resin' ),
			'실리콘수지 결합재 기반의 외벽 마감 플라스터입니다. 발수성과 통기성을 함께 갖췄습니다.' ),

		'AmphiSilan Fassadenputz Fein' => array( 'amphisilan-fassadenputz-fein', array( 'silicone-resin' ),
			'AmphiSilan 계열의 고운 입자(Fein) 마감 플라스터입니다. 매끈한 표면 질감을 냅니다.' ),

		/* ── 분산수지계 외장플라스터 ──────────────────── */
		'Muresko Fassadenputz' => array( 'muresko-fassadenputz', array( 'dispersion' ),
			'분산수지계 외벽 마감 플라스터입니다. 폭넓은 기재에 적용할 수 있습니다.' ),

		'Capatect Fassadenputz' => array( 'capatect-fassadenputz', array( 'dispersion' ),
			'분산수지계 외벽 마감 플라스터입니다. 일반적인 외벽 마감에 사용합니다.' ),

		/* ── 실리케이트 외장플라스터 ──────────────────── */
		'Sylitol Fassadenputz' => array( 'sylitol-fassadenputz', array( 'mineral' ),
			'실리케이트 기반의 미네랄 외벽 마감 플라스터입니다. 통기성이 높습니다.' ),

		/* ── 미네랄 외장플라스터 ──────────────────────── */
		'Mineral-Leichtputz' => array( 'mineral-leichtputz', array( 'mineral' ),
			'경량 미네랄 외벽 플라스터입니다. 미네랄 바탕면 마감에 사용합니다.' ),

		'Mineral-Feinspachtel 195' => array( 'mineral-feinspachtel-195', array( 'mineral' ),
			'미네랄 고운 마감용 퍼티(195)입니다. 표면을 매끈하게 다듬습니다.' ),

		'Modellier- und Spachtelputz 134' => array( 'modellier-spachtelputz-134', array( 'mineral' ),
			'성형·퍼티 겸용 플라스터(134)입니다. 조형 마감과 바탕 정리에 사용합니다.' ),

		'ArmaReno 700' => array( 'armareno-700', array( 'mineral' ),
			'미네랄 보강용 플라스터(700)입니다. 메쉬 매립·보강층으로 사용합니다.' ),

		/* ── 트라스 보수 플라스터 ─────────────────────── */
		'Histolith Trass-Sanierputz' => array( 'histolith-trass-sanierputz', array( 'mineral' ),
			'트라스 성분의 보수용 미네랄 플라스터입니다. 습기 있는 미네랄 바탕면 보수에 사용합니다.' ),

		/* ── 서늘한 계절용 첨가제 ─────────────────────── */
		/* ⚠️ 플라스터가 아니라 첨가제입니다. 카테고리를 옮기거나
		      빼야 할지 결정이 필요합니다. README 참고. */
		'Trocknungsbeschleuniger P' => array( 'trocknungsbeschleuniger-p', array(),
			'저온기용 건조촉진 첨가제(P)입니다. 서늘한 계절에 플라스터·도료 건조를 돕습니다.' ),
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

		update_post_meta( $post_id, 'summary', $summary );
	}

	update_option( 'caparol_products_seeded_plaster', 1 );
}
