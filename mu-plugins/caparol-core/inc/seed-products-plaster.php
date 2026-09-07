<?php
/**
 * 외장 플라스터 — 등록 틀 만들기 (한 번만 실행)
 *
 * 슬라이드 「Capatect 외장플라스터」의 제품을 임시글로 만듭니다.
 * 카테고리는 페인트가 아니라 플라스터/퍼티 > 외장 플라스터 입니다.
 *
 * ⚠️ 임시글(draft)입니다. 사이트에는 나오지 않습니다.
 *    요약은 Capatect 외장플라스터 카탈로그 슬라이드에서 옮긴 값입니다.
 *    용도·특징·기술 데이터·본문(입도별 사용량 표 포함)은
 *    docs/products/*-input.txt 및 *-body-blocks.txt 에 정리해 뒀습니다.
 *    관리자에서 각 제품을 열어 그 내용을 붙여넣고 공개해 주세요.
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
		'ThermoSan Fassadenputz NQG 써모산 외장플라스터' => array( 'thermosan-fassadenputz-nqg', array( 'self-cleaning' ),
			'하이브리드 결합재와 Nano-Quarz-Gitter(NQG) 기술을 적용한 시공 준비 완료형 경량 외장 플라스터입니다.' ),

		/* ── 실리콘 외장플라스터 ──────────────────────── */
		'AmphiSilan Fassadenputz 암피실란 외장플라스터' => array( 'amphisilan-fassadenputz', array( 'silicone-resin' ),
			'실리콘수지 결합재를 사용한 시공 준비 완료형 문양 플라스터 마감재입니다.' ),

		'AmphiSilan Fassadenputz FEIN 암피실란 외장플라스터' => array( 'amphisilan-fassadenputz-fein', array( 'silicone-resin' ),
			'DIN EN 15824 에 따른, 실리콘수지 결합재를 사용한 시공 준비 완료형 분산수지 결합 미세 플라스터입니다.' ),

		/* ── 분산수지계 외장플라스터 ──────────────────── */
		'Muresko Fassadenputz 무레스코 외장플라스터' => array( 'muresko-fassadenputz', array( 'dispersion' ),
			'DIN EN 15824 기준에 따른, SilaCryl® 기반의 개질형 실리콘수지 강화 외벽 플라스터입니다.' ),

		'Capatect Fassadenputz 외장플라스터' => array( 'capatect-fassadenputz', array( 'dispersion' ),
			'DIN EN 15824 에 따른 분산수지 결합 문양 플라스터 마감재입니다.' ),

		/* ── 실리케이트 외장플라스터 ──────────────────── */
		'Sylitol Fassadenputz 질리톨 외장플라스터' => array( 'sylitol-fassadenputz', array( 'mineral' ),
			'DIN EN 15824 에 따른 실리케이트 결합 문양 플라스터 마감재입니다.' ),

		/* ── 미네랄 외장플라스터 ──────────────────────── */
		'Capatect Mineral-Leichtputz 무기질 경량 플라스터' => array( 'mineral-leichtputz', array( 'mineral' ),
			'DIN EN 998-1 기준의 미네랄계 공장 배합 건조 모르타르로, 문지름·긁힘 플라스터 구조로 제공되는 실내외용 마감 플라스터입니다.' ),

		'Capatect Feinspachtel 195 무기질 경량 플라스터' => array( 'mineral-feinspachtel-195', array( 'mineral' ),
			'DIN EN 998-1 에 따른 미네랄계 공장 배합 건조 모르타르이며, 외부용 평활 플라스터 또는 펠트 마감 플라스터입니다.' ),

		'Capatect Modellier- und Spachtelputz 134 퍼티마감 플라스터' => array( 'modellier-spachtelputz-134', array( 'mineral' ),
			'EN 998-1 에 따른 석회-시멘트 기반의 미네랄계 공장 배합 건조 모르타르이며, 실내·외부용 마감 플라스터입니다.' ),

		'Capatect ArmaReno 700 아르마레노' => array( 'armareno-700', array( 'mineral' ),
			'폭넓은 적용 범위를 가진 미네랄계 공장 배합 건조 모르타르입니다. 단열재 접착·보강 미장재 겸용입니다.' ),

		/* ── 트라스 보수 플라스터 ─────────────────────── */
		'Histolith Trass-Sanierputz 트라스 보수 플라스터' => array( 'histolith-trass-sanierputz', array( 'mineral' ),
			'WTA 기준에 따라 습기와 염분으로 손상된 조적벽을 보수하기 위한, 수에비트 트라스를 함유한 트라스 보수 플라스터입니다.' ),

		/* ── 서늘한 계절용 첨가제 ─────────────────────── */
		/* ⚠️ 플라스터가 아니라 첨가제입니다. 카테고리를 옮기거나
		      빼야 할지 결정이 필요합니다. README 참고. */
		'Trocknungsbeschleuniger P 저온기용 건조촉진 첨가제' => array( 'trocknungsbeschleuniger-p', array(),
			'습하고 서늘한 계절에 AmphiSilan Fassadenputz K 와 함께 사용하여 건조를 촉진하기 위한 첨가제입니다.' ),
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
