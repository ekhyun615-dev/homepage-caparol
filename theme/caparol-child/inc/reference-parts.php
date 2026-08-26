<?php
/**
 * 시공사례 공통 부품
 *
 * 목록(/references), 지역별, 용도별 세 화면이 같은 부품을 씁니다.
 * ACF 함수는 쓰지 않고 get_post_meta 로만 값을 읽습니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 분류 필터 한 줄.
 *
 * @param string $taxonomy 분류 이름
 * @param string $label    왼쪽에 붙는 이름 (예: 지역)
 * @param int    $current  지금 보고 있는 term_id (전체면 0)
 * @param string $all_url  '전체' 가 가리킬 주소
 */
function caparol_ref_filter_row( $taxonomy, $label, $current = 0, $all_url = '' ) {

	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'parent'     => 0,
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}
	?>
	<div class="cl-filter__row">
		<span class="cl-filter__label"><?php echo esc_html( $label ); ?></span>
		<div class="cl-filter__chips">
			<?php if ( $all_url ) : ?>
				<a class="cl-chip<?php echo $current ? '' : ' is-active'; ?>" href="<?php echo esc_url( $all_url ); ?>">전체</a>
			<?php endif; ?>
			<?php
			foreach ( $terms as $term ) {
				$link = get_term_link( $term );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				printf(
					'<a class="cl-chip%1$s" href="%2$s">%3$s<span class="cl-chip__n">%4$d</span></a>',
					( (int) $term->term_id === (int) $current ) ? ' is-active' : '',
					esc_url( $link ),
					esc_html( $term->name ),
					(int) $term->count
				);
			}
			?>
		</div>
	</div>
	<?php
}

/** 지역 + 건물 용도 두 줄을 한 번에 */
function caparol_ref_filter( $current_region = 0, $current_type = 0 ) {
	$archive = get_post_type_archive_link( 'reference' );
	?>
	<div class="cl-filter cl-filter--stack" role="navigation" aria-label="시공사례 분류">
		<?php
		caparol_ref_filter_row( 'reference_region', '지역', $current_region, $archive );
		caparol_ref_filter_row( 'reference_type', '건물 용도', $current_type, $archive );
		?>
	</div>
	<?php
}

/** 사례 카드 한 장 — 루프 안에서 호출 */
function caparol_reference_card() {

	$id = get_the_ID();

	$loc  = get_post_meta( $id, 'location', true );
	$loc  = is_scalar( $loc ) ? trim( (string) $loc ) : '';

	$year = get_post_meta( $id, 'year', true );
	$year = is_scalar( $year ) ? trim( (string) $year ) : '';

	$types = get_the_terms( $id, 'reference_type' );
	$type  = ( $types && ! is_wp_error( $types ) ) ? $types[0]->name : '';

	// 위치 · 준공연도를 한 줄로. 빈 값은 빠집니다.
	$meta = array_filter( array( $loc, $year ? $year . '년' : '' ) );
	?>
	<article class="cl-card cl-card--ref">
		<a class="cl-card__link" href="<?php the_permalink(); ?>">

			<div class="cl-card__media">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'medium_large', array( 'class' => 'cl-card__img', 'loading' => 'lazy' ) ); ?>
				<?php else : ?>
					<span class="cl-card__img cl-card__img--empty" aria-hidden="true"></span>
				<?php endif; ?>
				<?php if ( $type ) : ?>
					<span class="cl-card__badge"><?php echo esc_html( $type ); ?></span>
				<?php endif; ?>
			</div>

			<div class="cl-card__body">
				<h2 class="cl-card__title"><?php the_title(); ?></h2>
				<?php if ( $meta ) : ?>
					<p class="cl-card__meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
				<?php endif; ?>
			</div>

		</a>
	</article>
	<?php
}

/**
 * 사용 제품 — ACF 관계 필드는 게시물 번호 배열로 저장됩니다.
 * ACF 플러그인 없이도 그대로 읽힙니다.
 *
 * @return int[] 유효한 제품 번호만
 */
function caparol_used_products( $post_id = 0 ) {

	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = get_post_meta( $post_id, 'used_products', true );

	if ( ! is_array( $raw ) ) {
		return array();
	}

	$ids = array();
	foreach ( $raw as $item ) {
		$id = is_object( $item ) && isset( $item->ID ) ? (int) $item->ID : (int) $item;
		if ( $id > 0 && 'product' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) {
			$ids[] = $id;
		}
	}

	return $ids;
}
