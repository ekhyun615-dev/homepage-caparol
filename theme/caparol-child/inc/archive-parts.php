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
function caparol_archive_head( $title, $desc = '', $count = null ) {
	?>
	<header class="cl-head">
		<h1 class="cl-head__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $desc ) : ?>
			<p class="cl-head__desc"><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
		<?php if ( null !== $count ) : ?>
			<p class="cl-head__count">총 <strong><?php echo (int) $count; ?></strong>개 제품</p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * 카테고리 필터 — 등록된 제품 카테고리를 자동으로 그립니다.
 * 카테고리를 추가하면 여기도 자동으로 늘어납니다. 코드를 고칠 필요 없습니다.
 *
 * @param int $current 현재 보고 있는 카테고리의 term_id (전체 목록이면 0)
 */
function caparol_product_filter( $current = 0 ) {

	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
		'orderby'    => 'term_order',
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$archive = get_post_type_archive_link( 'product' );
	?>
	<nav class="cl-filter" aria-label="제품 카테고리">
		<?php if ( $archive ) : ?>
			<a class="cl-chip<?php echo $current ? '' : ' is-active'; ?>" href="<?php echo esc_url( $archive ); ?>">전체</a>
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
	</nav>
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
