<?php
/**
 * 제품 상세 페이지
 *
 * 워드프레스는 커스텀 포스트 타입 `product` 의 단일 화면에서
 * 이 파일을 자동으로 찾아 씁니다. 헤더·푸터는 테마(Astra)의 것을 그대로 쓰므로
 * 메뉴는 유지됩니다.
 *
 * ── 설계 원칙 (중요) ─────────────────────────────────────────
 * ACF 함수(get_field)를 쓰지 않습니다.
 * ACF의 텍스트·텍스트영역 필드는 워드프레스의 일반 사용자 정의 필드로 저장되므로
 * get_post_meta() 로 그대로 읽을 수 있습니다.
 * ACF 플러그인이 꺼지거나 필드가 비어 있어도 이 페이지는 절대 죽지 않습니다.
 *
 * 문제가 생기면 이 파일 이름을 single-product.php.bak 으로 바꾸세요.
 * 워드프레스 기본 템플릿으로 돌아가 페이지는 무조건 열립니다.
 * 더 단순한 버전이 필요하면 single-product-minimal.php.txt 를 쓰세요.
 * ────────────────────────────────────────────────────────────
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$cp_id      = get_the_ID();
	$cp_summary = cp_meta( 'summary', $cp_id );
	$cp_usage   = cp_meta( 'usage', $cp_id );

	// 카테고리 — 오류(WP_Error)나 빈 값이 와도 안전하게 처리합니다
	$cp_cats = get_the_terms( $cp_id, 'product_cat' );
	$cp_cats = ( $cp_cats && ! is_wp_error( $cp_cats ) ) ? $cp_cats : array();

	// 본문 — 템플릿이 이미 그리는 표·특징 숏코드는 중복이므로 제거합니다
	$cp_raw  = str_ireplace(
		array( '[caparol_specs]', '[caparol_features]' ),
		'',
		get_the_content()
	);
	$cp_body = trim( $cp_raw ) !== '' ? apply_filters( 'the_content', $cp_raw ) : '';
	$cp_body = trim( wp_strip_all_tags( $cp_body ) ) !== '' ? $cp_body : '';

	$cp_features = function_exists( 'caparol_features_list' ) ? caparol_features_list( $cp_id ) : '';
	$cp_specs    = function_exists( 'caparol_specs_table' ) ? caparol_specs_table( $cp_id ) : '';

	$cp_has_image = has_post_thumbnail();
	?>

	<main class="cp-page" id="cp-main">
	<div class="cp-product">

		<!-- ── 상단: 이미지 + 제품 개요 ───────────────────── -->
		<section class="cp-hero<?php echo $cp_has_image ? '' : ' cp-hero--noimage'; ?>">

			<?php if ( $cp_has_image ) : ?>
				<figure class="cp-hero__media">
					<?php the_post_thumbnail( 'large', array( 'class' => 'cp-hero__image' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="cp-hero__body">

				<?php if ( $cp_cats ) : ?>
					<p class="cp-eyebrow">
						<?php
						$cp_labels = array();
						foreach ( $cp_cats as $cp_cat ) {
							$cp_link = get_term_link( $cp_cat );
							if ( is_wp_error( $cp_link ) ) {
								$cp_labels[] = esc_html( $cp_cat->name );
							} else {
								$cp_labels[] = '<a href="' . esc_url( $cp_link ) . '">' . esc_html( $cp_cat->name ) . '</a>';
							}
						}
						echo implode( '<span class="cp-eyebrow__sep">·</span>', $cp_labels );
						?>
					</p>
				<?php endif; ?>

				<h1 class="cp-hero__title"><?php the_title(); ?></h1>

				<?php if ( $cp_summary ) : ?>
					<p class="cp-hero__summary"><?php echo esc_html( $cp_summary ); ?></p>
				<?php endif; ?>

				<?php if ( $cp_usage ) : ?>
					<div class="cp-usage">
						<span class="cp-usage__label">용도 · 적용 부위</span>
						<p class="cp-usage__text"><?php echo nl2br( esc_html( $cp_usage ) ); ?></p>
					</div>
				<?php endif; ?>

				<div class="cp-hero__actions">
					<a class="cp-btn cp-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">견적 문의</a>
					<a class="cp-btn cp-btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">샘플 신청</a>
				</div>

			</div>
		</section>

		<!-- ── 본문 2단: 왼쪽 설명 / 오른쪽 데이터 ────────── -->
		<div class="cp-cols">

			<div class="cp-cols__main">

				<?php if ( $cp_features ) : ?>
					<section class="cp-section">
						<h2 class="cp-section__title">주요 특징</h2>
						<?php echo $cp_features; // 헬퍼 내부에서 이스케이프 처리됨 ?>
					</section>
				<?php endif; ?>

				<?php if ( $cp_body ) : ?>
					<section class="cp-section">
						<h2 class="cp-section__title">제품 설명</h2>
						<div class="cp-prose"><?php echo $cp_body; ?></div>
					</section>
				<?php endif; ?>

			</div>

			<aside class="cp-cols__side">
				<?php if ( $cp_specs ) : ?>
					<section class="cp-panel">
						<h2 class="cp-panel__title">기술 데이터</h2>
						<?php echo $cp_specs; ?>
					</section>
				<?php endif; ?>

				<section class="cp-panel cp-panel--contact">
					<h2 class="cp-panel__title">제품 문의</h2>
					<p class="cp-panel__text">현장 조건에 맞는 제품 선정과 시공 방법을 안내해 드립니다.</p>
					<a class="cp-btn cp-btn--primary cp-btn--block" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">상담 신청</a>
				</section>
			</aside>

		</div>

		</div><!-- /.cp-product -->

		<!-- ── 하단 CTA — 화면 전체 폭 ───────────────────── -->
		<section class="cp-cta">
			<div class="cp-cta__in">
				<p class="cp-cta__lead">Caparol Korea</p>
				<h2 class="cp-cta__title">현장에 맞는 제품을<br>함께 찾아드립니다</h2>
				<a class="cp-btn cp-btn--dark cp-btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">상담 신청하기</a>
			</div>
		</section>

	</main>

	<?php
endwhile;

get_footer();
