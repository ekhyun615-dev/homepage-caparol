<?php
/**
 * 시공사례 상세 — /references/[현장명]
 *
 * 제품 상세와 같은 원칙입니다. ACF 함수를 쓰지 않고 get_post_meta 로만 읽으므로
 * 필드가 비어 있거나 플러그인이 꺼져도 화면이 죽지 않습니다.
 *
 * 사진은 본문의 갤러리 블록으로 넣으시면 됩니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$rid = get_the_ID();

	$loc  = cp_meta( 'location', $rid );
	$year = cp_meta( 'year', $rid );
	$area = cp_meta( 'area', $rid );

	$types   = get_the_terms( $rid, 'reference_type' );
	$type    = ( $types && ! is_wp_error( $types ) ) ? $types[0] : null;
	$regions = get_the_terms( $rid, 'reference_region' );
	$region  = ( $regions && ! is_wp_error( $regions ) ) ? $regions[0] : null;

	$products = caparol_used_products( $rid );

	// 제목 아래 한 줄 요약
	$line = array_filter( array(
		$loc,
		$type ? $type->name : '',
		$year ? $year . '년 준공' : '',
	) );
	?>

	<main class="cp-page rf" id="cp-main">

		<!-- ── 대표 사진 ─────────────────────────────── -->
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="rf-hero">
				<?php the_post_thumbnail( 'full', array( 'class' => 'rf-hero__img' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="rf__in">

			<header class="rf-head">
				<?php if ( $region ) :
					$rlink = get_term_link( $region ); ?>
					<p class="cp-eyebrow">
						<?php if ( is_wp_error( $rlink ) ) : ?>
							<?php echo esc_html( $region->name ); ?>
						<?php else : ?>
							<a href="<?php echo esc_url( $rlink ); ?>"><?php echo esc_html( $region->name ); ?></a>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<h1 class="rf-head__title"><?php the_title(); ?></h1>

				<?php if ( $line ) : ?>
					<p class="rf-head__line"><?php echo esc_html( implode( '  ·  ', $line ) ); ?></p>
				<?php endif; ?>
			</header>

			<div class="cp-cols">

				<div class="cp-cols__main">
					<?php
					$body = trim( wp_strip_all_tags( get_the_content() ) );
					if ( '' !== $body || has_blocks( get_the_content() ) ) :
						?>
						<section class="cp-section">
							<div class="cp-prose"><?php the_content(); ?></div>
						</section>
					<?php endif; ?>

					<!-- ── 사용 제품 ───────────────────── -->
					<?php if ( $products ) : ?>
						<section class="cp-section">
							<h2 class="cp-section__title">사용 제품</h2>
							<ul class="rf-products">
								<?php foreach ( $products as $pid ) :
									$cats  = get_the_terms( $pid, 'product_cat' );
									$plabel = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
									?>
									<li class="rf-product">
										<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>">
											<span class="rf-product__media">
												<?php if ( has_post_thumbnail( $pid ) ) {
													echo get_the_post_thumbnail( $pid, 'medium', array( 'loading' => 'lazy' ) );
												} ?>
											</span>
											<span class="rf-product__body">
												<?php if ( $plabel ) : ?>
													<span class="rf-product__cat"><?php echo esc_html( $plabel ); ?></span>
												<?php endif; ?>
												<span class="rf-product__name"><?php echo esc_html( get_the_title( $pid ) ); ?></span>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>
				</div>

				<!-- ── 현장 정보 ───────────────────────── -->
				<aside class="cp-cols__side">
					<?php
					$rows = array();
					if ( $loc )    { $rows['위치']      = $loc; }
					if ( $type )   { $rows['건물 용도'] = $type->name; }
					if ( $year )   { $rows['준공 연도'] = $year . '년'; }
					if ( $area )   { $rows['시공 면적'] = $area; }
					?>
					<?php if ( $rows ) : ?>
						<section class="cp-panel">
							<h2 class="cp-panel__title">현장 정보</h2>
							<table class="caparol-specs">
								<tbody>
									<?php foreach ( $rows as $k => $v ) : ?>
										<tr><th><?php echo esc_html( $k ); ?></th><td><?php echo esc_html( $v ); ?></td></tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</section>
					<?php endif; ?>

					<section class="cp-panel cp-panel--contact">
						<h2 class="cp-panel__title">비슷한 현장이신가요?</h2>
						<p class="cp-panel__text">현장 조건을 알려주시면 이 사례에 쓰인 제품과 시공 방법을 안내해 드립니다.</p>
						<a class="cp-btn cp-btn--primary cp-btn--block" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">상담 신청</a>
					</section>
				</aside>

			</div>

			<!-- ── 다른 사례 ───────────────────────────── -->
			<?php
			$more_args = array(
				'post_type'           => 'reference',
				'posts_per_page'      => 3,
				'post__not_in'        => array( $rid ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			);
			if ( $type ) {
				$more_args['tax_query'] = array( array(
					'taxonomy' => 'reference_type',
					'field'    => 'term_id',
					'terms'    => $type->term_id,
				) );
			}
			$more = new WP_Query( $more_args );

			// 같은 용도의 사례가 없으면 최신 사례로 대체합니다
			if ( ! $more->have_posts() && $type ) {
				unset( $more_args['tax_query'] );
				$more = new WP_Query( $more_args );
			}
			?>
			<?php if ( $more->have_posts() ) : ?>
				<section class="rf-more">
					<header class="fp-head">
						<h2 class="fp-head__title">다른 시공사례</h2>
						<a class="fp-head__more" href="<?php echo esc_url( get_post_type_archive_link( 'reference' ) ); ?>">사례 전체 보기</a>
					</header>
					<div class="cl-grid cl-grid--3">
						<?php while ( $more->have_posts() ) { $more->the_post(); caparol_reference_card(); } ?>
					</div>
				</section>
			<?php endif; wp_reset_postdata(); ?>

		</div>
	</main>

	<?php
endwhile;

get_footer();
