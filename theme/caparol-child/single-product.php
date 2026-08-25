<?php
/**
 * 제품 상세 페이지
 *
 * Elementor 템플릿 대신 이 파일이 제품 상세 화면을 그립니다.
 * 워드프레스는 커스텀 포스트 타입 `product` 의 단일 화면에서
 * single-product.php 를 자동으로 찾아 씁니다.
 *
 * 헤더·푸터는 테마(Astra)의 것을 그대로 쓰므로 메뉴는 유지됩니다.
 *
 * ⚠️ Elementor 테마 빌더에 "제품"을 대상으로 하는 템플릿이 남아 있으면
 *    그쪽이 우선합니다. Theme Builder에서 그 템플릿을 삭제하거나
 *    표시 조건을 해제해야 이 파일이 적용됩니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id  = get_the_ID();
	$acf      = function_exists( 'get_field' );
	$summary  = $acf ? get_field( 'summary', $post_id ) : '';
	$usage    = $acf ? get_field( 'usage', $post_id ) : '';
	$colors   = $acf ? get_field( 'product_colors', $post_id ) : array();
	$docs     = $acf ? get_field( 'documents', $post_id ) : array();
	$cats     = get_the_terms( $post_id, 'product_cat' );
	?>

	<main class="cp-product">

		<!-- ── 상단: 이미지 + 제품 개요 ───────────────────── -->
		<section class="cp-product__hero">

			<div class="cp-product__media">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large', array( 'class' => 'cp-product__image' ) ); ?>
				<?php else : ?>
					<div class="cp-product__image cp-product__image--empty" aria-hidden="true"></div>
				<?php endif; ?>
			</div>

			<div class="cp-product__intro">

				<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
					<p class="cp-product__cat">
						<?php foreach ( $cats as $cat ) : ?>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>

				<h1 class="cp-product__title"><?php the_title(); ?></h1>

				<?php if ( $summary ) : ?>
					<p class="cp-product__summary"><?php echo esc_html( $summary ); ?></p>
				<?php endif; ?>

				<?php if ( $usage ) : ?>
					<dl class="cp-product__usage">
						<dt>용도 · 적용 부위</dt>
						<dd><?php echo nl2br( esc_html( $usage ) ); ?></dd>
					</dl>
				<?php endif; ?>

				<div class="cp-product__actions">
					<a class="cp-btn cp-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">견적 문의</a>
					<a class="cp-btn cp-btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">샘플 신청</a>
				</div>

			</div>
		</section>

		<!-- ── 주요 특징 ─────────────────────────────────── -->
		<?php $features = function_exists( 'caparol_features_list' ) ? caparol_features_list( $post_id ) : ''; ?>
		<?php if ( $features ) : ?>
			<section class="cp-section">
				<h2 class="cp-section__title">주요 특징</h2>
				<?php echo $features; // 내부에서 이스케이프 처리됨 ?>
			</section>
		<?php endif; ?>

		<!-- ── 기술 데이터 ───────────────────────────────── -->
		<?php $specs = function_exists( 'caparol_specs_table' ) ? caparol_specs_table( $post_id ) : ''; ?>
		<?php if ( $specs ) : ?>
			<section class="cp-section">
				<h2 class="cp-section__title">기술 데이터</h2>
				<?php echo $specs; ?>
			</section>
		<?php endif; ?>

		<!-- ── 제품 설명 (본문) ──────────────────────────── -->
		<?php if ( trim( get_the_content() ) ) : ?>
			<section class="cp-section">
				<h2 class="cp-section__title">제품 설명</h2>
				<div class="cp-prose"><?php the_content(); ?></div>
			</section>
		<?php endif; ?>

		<!-- ── 색상 ──────────────────────────────────────── -->
		<?php if ( $colors ) : ?>
			<section class="cp-section">
				<h2 class="cp-section__title">색상</h2>
				<ul class="cp-colors">
					<?php foreach ( $colors as $color ) :
						$cid  = is_object( $color ) ? $color->ID : (int) $color;
						$hex  = $acf ? get_field( 'color_hex', $cid ) : '';
						$code = $acf ? get_field( 'color_code', $cid ) : '';
						?>
						<li class="cp-color">
							<a href="<?php echo esc_url( get_permalink( $cid ) ); ?>">
								<span class="cp-color__chip" style="background:<?php echo esc_attr( $hex ?: '#e5e7eb' ); ?>"></span>
								<span class="cp-color__name"><?php echo esc_html( get_the_title( $cid ) ); ?></span>
								<?php if ( $code ) : ?>
									<span class="cp-color__code"><?php echo esc_html( $code ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="cp-note">모니터 환경에 따라 실제 색상과 차이가 있습니다. 시공 전 실물 색상집으로 확인하시기 바랍니다.</p>
			</section>
		<?php endif; ?>

		<!-- ── 관련 자료 ─────────────────────────────────── -->
		<?php if ( $docs ) : ?>
			<section class="cp-section">
				<h2 class="cp-section__title">기술자료 다운로드</h2>
				<ul class="cp-docs">
					<?php foreach ( $docs as $doc ) :
						$did  = is_object( $doc ) ? $doc->ID : (int) $doc;
						$file = $acf ? get_field( 'file', $did ) : '';
						$url  = is_array( $file ) ? ( $file['url'] ?? '' ) : $file;
						$note = $acf ? get_field( 'doc_note', $did ) : '';
						?>
						<li class="cp-doc">
							<a href="<?php echo esc_url( $url ?: get_permalink( $did ) ); ?>"<?php echo $url ? ' target="_blank" rel="noopener"' : ''; ?>>
								<span class="cp-doc__name"><?php echo esc_html( get_the_title( $did ) ); ?></span>
								<?php if ( $note ) : ?>
									<span class="cp-doc__note"><?php echo esc_html( $note ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<!-- ── 이 제품 시공사례 ──────────────────────────── -->
		<?php
		$refs = new WP_Query( array(
			'post_type'      => 'reference',
			'posts_per_page' => 6,
			'meta_query'     => array(
				array(
					'key'     => 'used_products',
					'value'   => '"' . $post_id . '"',
					'compare' => 'LIKE',
				),
			),
		) );
		?>
		<?php if ( $refs->have_posts() ) : ?>
			<section class="cp-section">
				<h2 class="cp-section__title">이 제품 시공사례</h2>
				<ul class="cp-refs">
					<?php while ( $refs->have_posts() ) : $refs->the_post(); ?>
						<li class="cp-ref">
							<a href="<?php the_permalink(); ?>">
								<span class="cp-ref__thumb">
									<?php if ( has_post_thumbnail() ) {
										the_post_thumbnail( 'medium' );
									} ?>
								</span>
								<span class="cp-ref__title"><?php the_title(); ?></span>
								<?php $loc = $acf ? get_field( 'location' ) : ''; ?>
								<?php if ( $loc ) : ?>
									<span class="cp-ref__meta"><?php echo esc_html( $loc ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>
			</section>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>

		<!-- ── 문의 CTA ──────────────────────────────────── -->
		<section class="cp-cta">
			<p class="cp-cta__lead">이 제품에 대해 더 알고 싶으신가요?</p>
			<h2 class="cp-cta__title">현장에 맞는 제품을 함께 찾아드립니다</h2>
			<a class="cp-btn cp-btn--primary cp-btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">상담 신청하기</a>
		</section>

	</main>

	<?php
endwhile;

get_footer();
