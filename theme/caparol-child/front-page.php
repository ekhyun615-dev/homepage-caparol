<?php
/**
 * 홈 화면
 *
 * 워드프레스는 첫 화면에서 이 파일을 가장 먼저 찾습니다.
 * "설정 → 읽기" 가 최신 글이든 정적 페이지든 상관없이 이 화면이 나옵니다.
 * (설정을 건드릴 필요가 없다는 뜻입니다)
 *
 * ── 콘텐츠가 없어도 안전합니다 ──────────────────────────────
 * 시공사례·기술자료·색상·공지가 아직 없으면 그 섹션은 아예 나오지 않습니다.
 * 빈 칸이나 "내용 없음" 이 노출되지 않습니다.
 * 나중에 글을 등록하면 그 순간 섹션이 자동으로 나타납니다.
 *
 * 첫 화면 배경 사진
 *   설정 → 읽기 에서 홈으로 지정한 페이지에 "대표 이미지"를 넣으면
 *   히어로 배경으로 깔립니다. 없으면 깔끔한 흰 배경으로 나옵니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$i = caparol_info();

// 히어로 배경 — 홈으로 지정한 페이지의 대표 이미지
$front_id  = (int) get_option( 'page_on_front' );
$hero_img  = $front_id ? get_the_post_thumbnail_url( $front_id, 'full' ) : '';
?>

<main class="cp-page fp" id="cp-main">

	<!-- ─────────────────────────── 1. 히어로 -->
	<section class="fp-hero<?php echo $hero_img ? ' fp-hero--photo' : ''; ?>"
		<?php if ( $hero_img ) : ?>style="background-image:url('<?php echo esc_url( $hero_img ); ?>')"<?php endif; ?>>
		<div class="fp-hero__in">
			<?php if ( $i['hero_lead'] ) : ?>
				<p class="fp-hero__lead"><?php echo esc_html( $i['hero_lead'] ); ?></p>
			<?php endif; ?>

			<h1 class="fp-hero__title"><?php echo nl2br( esc_html( $i['hero_title'] ) ); ?></h1>

			<?php if ( $i['hero_text'] ) : ?>
				<p class="fp-hero__text"><?php echo esc_html( $i['hero_text'] ); ?></p>
			<?php endif; ?>

			<p class="fp-hero__actions">
				<a class="cp-btn cp-btn--primary cp-btn--lg" href="<?php echo esc_url( home_url( '/products/' ) ); ?>">제품 보기</a>
				<a class="cp-btn cp-btn--ghost cp-btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">문의하기</a>
			</p>
		</div>
	</section>

	<!-- ─────────────────────────── 2. 신뢰 지표 -->
	<?php if ( ! empty( $i['stats'] ) && is_array( $i['stats'] ) ) : ?>
		<section class="fp-stats">
			<div class="fp-stats__in">
				<?php foreach ( $i['stats'] as $stat ) :
					if ( ! is_array( $stat ) || count( $stat ) < 2 ) { continue; } ?>
					<div class="fp-stat">
						<strong class="fp-stat__num"><?php echo esc_html( $stat[0] ); ?></strong>
						<span class="fp-stat__label"><?php echo esc_html( $stat[1] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- ─────────────────────────── 3. 제품 카테고리 -->
	<?php
	$cats = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
	) );
	?>
	<?php if ( ! is_wp_error( $cats ) && $cats ) : ?>
		<section class="fp-section">
			<div class="fp-section__in">

				<header class="fp-head">
					<h2 class="fp-head__title">제품</h2>
					<p class="fp-head__desc">현장 조건에 맞는 제품군을 선택하세요.</p>
					<a class="fp-head__more" href="<?php echo esc_url( home_url( '/products/' ) ); ?>">전체 제품 보기</a>
				</header>

				<ul class="fp-cats">
					<?php foreach ( $cats as $cat ) :
						$link = get_term_link( $cat );
						if ( is_wp_error( $link ) ) { continue; } ?>
						<li class="fp-cat">
							<a href="<?php echo esc_url( $link ); ?>">
								<span class="fp-cat__name"><?php echo esc_html( $cat->name ); ?></span>
								<?php if ( $cat->count ) : ?>
									<span class="fp-cat__n"><?php echo (int) $cat->count; ?>개 제품</span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

			</div>
		</section>
	<?php endif; ?>

	<!-- ─────────────────────────── 4. 시공사례 -->
	<?php
	$refs = new WP_Query( array(
		'post_type'           => 'reference',
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	?>
	<?php if ( $refs->have_posts() ) : ?>
		<section class="fp-section fp-section--alt">
			<div class="fp-section__in">

				<header class="fp-head">
					<h2 class="fp-head__title">시공사례</h2>
					<p class="fp-head__desc">실제 현장에서 Caparol 이 어떻게 쓰였는지 보실 수 있습니다.</p>
					<a class="fp-head__more" href="<?php echo esc_url( home_url( '/references/' ) ); ?>">사례 전체 보기</a>
				</header>

				<div class="fp-refs">
					<?php while ( $refs->have_posts() ) : $refs->the_post(); ?>
						<article class="fp-ref">
							<a href="<?php the_permalink(); ?>">
								<span class="fp-ref__media">
									<?php if ( has_post_thumbnail() ) {
										the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) );
									} ?>
								</span>
								<span class="fp-ref__title"><?php the_title(); ?></span>
								<?php $loc = get_post_meta( get_the_ID(), 'location', true ); ?>
								<?php if ( is_scalar( $loc ) && '' !== trim( (string) $loc ) ) : ?>
									<span class="fp-ref__meta"><?php echo esc_html( $loc ); ?></span>
								<?php endif; ?>
							</a>
						</article>
					<?php endwhile; ?>
				</div>

			</div>
		</section>
	<?php endif; wp_reset_postdata(); ?>

	<!-- ─────────────────────────── 5. 기술자료 -->
	<?php
	$docs = new WP_Query( array(
		'post_type'           => 'document',
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	?>
	<?php if ( $docs->have_posts() ) : ?>
		<section class="fp-section">
			<div class="fp-section__in">

				<header class="fp-head">
					<h2 class="fp-head__title">기술자료</h2>
					<p class="fp-head__desc">카탈로그 · 제품 데이터시트 · 시공지침 · 시험성적서</p>
					<a class="fp-head__more" href="<?php echo esc_url( home_url( '/downloads/' ) ); ?>">자료실 전체 보기</a>
				</header>

				<ul class="fp-docs">
					<?php while ( $docs->have_posts() ) : $docs->the_post(); ?>
						<li class="fp-doc">
							<a href="<?php the_permalink(); ?>">
								<span class="fp-doc__name"><?php the_title(); ?></span>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>

			</div>
		</section>
	<?php endif; wp_reset_postdata(); ?>

	<!-- ─────────────────────────── 6. 공지사항 -->
	<?php
	$news = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	?>
	<?php if ( $news->have_posts() ) : ?>
		<section class="fp-section fp-section--alt">
			<div class="fp-section__in">

				<header class="fp-head">
					<h2 class="fp-head__title">공지사항</h2>
					<a class="fp-head__more" href="<?php echo esc_url( home_url( '/notice/' ) ); ?>">전체 보기</a>
				</header>

				<ul class="fp-news">
					<?php while ( $news->have_posts() ) : $news->the_post(); ?>
						<li class="fp-news__item">
							<a href="<?php the_permalink(); ?>">
								<span class="fp-news__title"><?php the_title(); ?></span>
								<time class="fp-news__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>

			</div>
		</section>
	<?php endif; wp_reset_postdata(); ?>

	<!-- ─────────────────────────── 7. 문의 CTA -->
	<section class="fp-cta">
		<div class="fp-cta__in">
			<p class="fp-cta__lead">제품 선정이 고민되시나요?</p>
			<h2 class="fp-cta__title">현장 조건을 알려주시면<br>맞는 제품을 찾아드립니다</h2>
			<p class="fp-cta__actions">
				<a class="cp-btn cp-btn--primary cp-btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">문의하기</a>
				<?php if ( $i['tel'] ) : ?>
					<a class="cp-btn cp-btn--ghost cp-btn--lg" href="tel:<?php echo esc_attr( $i['tel_raw'] ); ?>"><?php echo esc_html( $i['tel'] ); ?></a>
				<?php endif; ?>
			</p>
		</div>
	</section>

</main>

<?php
get_footer();
