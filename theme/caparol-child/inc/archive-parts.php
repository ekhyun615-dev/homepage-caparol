<?php
/**
 * 목록 화면 공통 부품
 *
 * /products (전체) 와 /products/category/xxx (카테고리별) 두 화면이
 * 같은 부품을 씁니다. 카드 디자인을 한 번 고치면 두 곳에 같이 반영됩니다.
 *
 * 제품 상세와 마찬가지로 ACF 함수를 쓰지 않습니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 목록 상단 — 제목 + 설명 + 개수
 */
function caparol_archive_head( $title, $desc = '', $count = null, $unit = '개 제품' ) {
	?>
	<header class="cl-head">
		<h1 class="cl-head__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $desc ) : ?>
			<p class="cl-head__desc"><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
		<?php if ( null !== $count ) : ?>
			<p class="cl-head__count">총 <strong><?php echo (int) $count; ?></strong><?php echo esc_html( $unit ); ?></p>
		<?php endif; ?>
	</header>
	<?php
}

/** 지금 화면의 기준 주소 (필터 링크를 만들 때 씁니다) */
function caparol_current_archive_url() {

	$term = get_queried_object();

	if ( $term && isset( $term->taxonomy ) && 'product_cat' === $term->taxonomy ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	$archive = get_post_type_archive_link( 'product' );
	return $archive ? $archive : home_url( '/products/' );
}

/** 지금 걸려 있는 특성 필터 (없으면 빈 문자열) */
function caparol_current_feature() {
	return isset( $_GET['cp_feature'] ) ? sanitize_title( wp_unslash( $_GET['cp_feature'] ) ) : '';
}

/** 알약 버튼 한 줄 */
function caparol_filter_row( $label, $chips ) {

	if ( empty( $chips ) ) {
		return;
	}
	?>
	<div class="cl-filter__row">
		<span class="cl-filter__label"><?php echo esc_html( $label ); ?></span>
		<div class="cl-filter__chips">
			<?php
			foreach ( $chips as $chip ) {
				printf(
					'<a class="cl-chip%1$s" href="%2$s">%3$s%4$s</a>',
					! empty( $chip['active'] ) ? ' is-active' : '',
					esc_url( $chip['url'] ),
					esc_html( $chip['label'] ),
					isset( $chip['count'] ) ? '<span class="cl-chip__n">' . (int) $chip['count'] . '</span>' : ''
				);
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * 제품 목록 필터 — 최대 세 줄.
 *
 *   제품군   전체 · 프라이머 · 페인트 · …          (항상)
 *   세부     인테리어 페인트 · 외장 페인트          (하위 분류가 있을 때만)
 *   특성     습윤마모 1등급 · 미네랄 · 라텍스 · …    (제품에 특성이 붙어 있을 때만)
 *
 * 필요 없는 줄은 알아서 사라집니다. 분류를 추가하면 버튼도 자동으로 늘어납니다.
 *
 * @param int $current 지금 보고 있는 제품 카테고리 term_id (전체 목록이면 0)
 */
function caparol_product_filter( $current = 0 ) {

	$archive = caparol_current_archive_url();
	$feature = caparol_current_feature();

	// 지금 항목과 그 부모를 찾습니다
	$term   = $current ? get_term( $current, 'product_cat' ) : null;
	$term   = ( $term && ! is_wp_error( $term ) ) ? $term : null;
	$parent = ( $term && $term->parent ) ? get_term( $term->parent, 'product_cat' ) : null;
	$parent = ( $parent && ! is_wp_error( $parent ) ) ? $parent : null;

	// 최상위에서 강조할 항목 — 하위에 들어와 있으면 그 부모를 강조합니다
	$top_active = $parent ? (int) $parent->term_id : ( $term ? (int) $term->term_id : 0 );

	/* ── 1줄: 제품군 ─────────────────────────────── */
	$tops = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
	) );

	$chips = array();
	$all   = get_post_type_archive_link( 'product' );
	if ( $all ) {
		$chips[] = array( 'label' => '전체', 'url' => $all, 'active' => ! $top_active );
	}
	if ( ! is_wp_error( $tops ) ) {
		foreach ( $tops as $t ) {
			$link = get_term_link( $t );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$chips[] = array(
				'label'  => $t->name,
				'url'    => $link,
				'count'  => $t->count,
				'active' => ( (int) $t->term_id === $top_active ),
			);
		}
	}

	/* ── 2줄: 세부 분류 ──────────────────────────── */
	$sub_chips = array();
	$sub_of    = $parent ? $parent : $term;   // 하위에 있으면 형제들, 부모에 있으면 자식들

	if ( $sub_of ) {
		$subs = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => $sub_of->term_id,
		) );

		if ( ! is_wp_error( $subs ) && $subs ) {
			$plink = get_term_link( $sub_of );
			if ( ! is_wp_error( $plink ) ) {
				$sub_chips[] = array(
					'label'  => $sub_of->name . ' 전체',
					'url'    => $plink,
					'active' => ( $term && (int) $term->term_id === (int) $sub_of->term_id ),
				);
			}
			foreach ( $subs as $sub ) {
				$link = get_term_link( $sub );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				$sub_chips[] = array(
					'label'  => $sub->name,
					'url'    => $link,
					'count'  => $sub->count,
					'active' => ( $term && (int) $term->term_id === (int) $sub->term_id ),
				);
			}
		}
	}

	/* ── 3줄: 특성 ───────────────────────────────── */
	$feat_chips = array();
	$features   = get_terms( array(
		'taxonomy'   => 'product_feature',
		'hide_empty' => true,          // 제품에 붙어 있는 것만
	) );

	if ( ! is_wp_error( $features ) && $features ) {

		$feat_chips[] = array(
			'label'  => '전체',
			'url'    => remove_query_arg( 'cp_feature', $archive ),
			'active' => ( '' === $feature ),
		);

		foreach ( $features as $f ) {
			$feat_chips[] = array(
				'label'  => $f->name,
				'url'    => add_query_arg( 'cp_feature', $f->slug, $archive ),
				'active' => ( $feature === $f->slug ),
			);
		}
	}

	if ( ! $chips && ! $sub_chips && ! $feat_chips ) {
		return;
	}
	?>
	<div class="cl-filter cl-filter--stack" role="navigation" aria-label="제품 분류">
		<?php
		caparol_filter_row( '제품군', $chips );
		caparol_filter_row( '세부', $sub_chips );
		caparol_filter_row( '특성', $feat_chips );
		?>
	</div>
	<?php
}

/**
 * 제품 카드 한 장. 루프 안에서 호출합니다.
 */
function caparol_product_card() {

	$id = get_the_ID();

	// 요약문 — ACF summary 필드가 있으면 그걸, 없으면 워드프레스 요약을 씁니다
	$summary = get_post_meta( $id, 'summary', true );
	$summary = is_scalar( $summary ) ? trim( (string) $summary ) : '';
	if ( '' === $summary ) {
		$summary = trim( wp_strip_all_tags( get_the_excerpt() ) );
	}
	$summary = wp_trim_words( $summary, 26, '…' );

	// 카테고리 라벨 — 첫 번째 것만 씁니다
	$cats  = get_the_terms( $id, 'product_cat' );
	$label = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
	?>
	<article class="cl-card">
		<a class="cl-card__link" href="<?php the_permalink(); ?>">

			<div class="cl-card__media">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'medium_large', array( 'class' => 'cl-card__img', 'loading' => 'lazy' ) ); ?>
				<?php else : ?>
					<span class="cl-card__img cl-card__img--empty" aria-hidden="true"></span>
				<?php endif; ?>
			</div>

			<div class="cl-card__body">
				<?php if ( $label ) : ?>
					<p class="cl-card__cat"><?php echo esc_html( $label ); ?></p>
				<?php endif; ?>

				<h2 class="cl-card__title"><?php the_title(); ?></h2>

				<?php if ( $summary ) : ?>
					<p class="cl-card__summary"><?php echo esc_html( $summary ); ?></p>
				<?php endif; ?>

				<span class="cl-card__more">자세히 보기</span>
			</div>

		</a>
	</article>
	<?php
}

/**
 * 등록된 제품이 없을 때
 */
function caparol_archive_empty( $message = '등록된 제품이 없습니다.' ) {
	?>
	<p class="cl-empty"><?php echo esc_html( $message ); ?></p>
	<?php
}

/**
 * 페이지 넘김
 */
function caparol_archive_pagination() {
	$links = paginate_links( array(
		'prev_text' => '이전',
		'next_text' => '다음',
		'type'      => 'array',
	) );

	if ( empty( $links ) ) {
		return;
	}

	echo '<nav class="cl-pager" aria-label="페이지 넘김">' . implode( '', $links ) . '</nav>';
}
