<?php
/**
 * 브랜드 소개 — /about
 *
 * 슬러그가 about 인 페이지에 워드프레스가 자동으로 이 파일을 씁니다.
 *
 * 글은 두 군데에서 옵니다.
 *   - 짧은 문구·연혁·카드  → inc/about-content.php
 *   - 긴 서술형 회사 이야기 → 이 페이지의 본문 (워드프레스 편집기)
 * 본문을 비워두면 그 섹션은 나오지 않습니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$a = caparol_about();
$i = caparol_info();
?>

<main class="cp-page ab" id="cp-main">

	<!-- ── 첫 화면 ───────────────────────────────── -->
	<section class="ab-hero">
		<div class="ab-hero__in">
			<?php if ( $a['lead'] ) : ?>
				<p class="fp-hero__lead"><?php echo esc_html( $a['lead'] ); ?></p>
			<?php endif; ?>
			<h1 class="ab-hero__title"><?php echo nl2br( esc_html( $a['title'] ) ); ?></h1>
			<?php if ( $a['text'] ) : ?>
				<p class="ab-hero__text"><?php echo esc_html( $a['text'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<!-- ── 신뢰 지표 — 홈과 같은 값을 씁니다 ────────── -->
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

	<!-- ── 회사 이야기 — 페이지 본문 ────────────────── -->
	<?php
	$img  = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	$body = '';
	while ( have_posts() ) {
		the_post();
		if ( '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) {
			$body = apply_filters( 'the_content', get_the_content() );
		}
	}
	?>
	<?php if ( $body || $img ) : ?>
		<section class="fp-section">
			<div class="fp-section__in">
				<div class="ab-story<?php echo $img ? '' : ' ab-story--text'; ?>">
					<?php if ( $img ) : ?>
						<figure class="ab-story__media">
							<img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy">
						</figure>
					<?php endif; ?>
					<?php if ( $body ) : ?>
						<div class="ab-story__body cp-prose"><?php echo $body; ?></div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── 왜 Caparol 인가 ──────────────────────────── -->
	<?php if ( ! empty( $a['points'] ) ) : ?>
		<section class="fp-section fp-section--alt">
			<div class="fp-section__in">
				<header class="fp-head">
					<h2 class="fp-head__title">왜 Caparol 인가</h2>
				</header>
				<ul class="ab-points">
					<?php foreach ( $a['points'] as $n => $p ) : ?>
						<li class="ab-point">
							<span class="ab-point__n"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
							<h3 class="ab-point__title"><?php echo esc_html( $p['title'] ); ?></h3>
							<p class="ab-point__text"><?php echo esc_html( $p['text'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── 연혁 ─────────────────────────────────────── -->
	<?php
	// 연도와 내용이 모두 빈 줄은 버립니다
	$history = array();
	foreach ( (array) $a['history'] as $h ) {
		if ( is_array( $h ) && count( $h ) >= 2 && ( '' !== trim( $h[0] ) || '' !== trim( $h[1] ) ) ) {
			$history[] = $h;
		}
	}
	?>
	<?php if ( $history ) : ?>
		<section class="fp-section">
			<div class="fp-section__in">
				<header class="fp-head">
					<h2 class="fp-head__title">연혁</h2>
				</header>
				<ol class="ab-history">
					<?php foreach ( $history as $h ) : ?>
						<li class="ab-history__item">
							<span class="ab-history__year"><?php echo esc_html( '' !== trim( $h[0] ) ? $h[0] : '·' ); ?></span>
							<span class="ab-history__text"><?php echo esc_html( $h[1] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── 카파롤코리아가 하는 일 ───────────────────── -->
	<?php if ( ! empty( $a['roles'] ) ) : ?>
		<section class="fp-section fp-section--alt">
			<div class="fp-section__in">
				<header class="fp-head">
					<h2 class="fp-head__title"><?php echo esc_html( $a['role_title'] ); ?></h2>
					<?php if ( $a['role_text'] ) : ?>
						<p class="fp-head__desc"><?php echo esc_html( $a['role_text'] ); ?></p>
					<?php endif; ?>
				</header>
				<ul class="ab-roles">
					<?php foreach ( $a['roles'] as $r ) :
						if ( ! is_array( $r ) || count( $r ) < 2 ) { continue; } ?>
						<li class="ab-role">
							<h3 class="ab-role__title"><?php echo esc_html( $r[0] ); ?></h3>
							<p class="ab-role__text"><?php echo esc_html( $r[1] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<!-- ── 마무리 ───────────────────────────────────── -->
	<section class="fp-cta">
		<div class="fp-cta__in">
			<p class="fp-cta__lead">Caparol Korea</p>
			<h2 class="fp-cta__title">어떤 현장이신가요?</h2>
			<p class="fp-cta__actions">
				<a class="cp-btn cp-btn--primary cp-btn--lg" href="<?php echo esc_url( home_url( '/products/' ) ); ?>">제품 보기</a>
				<a class="cp-btn cp-btn--ghost cp-btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">문의하기</a>
			</p>
		</div>
	</section>

</main>

<?php
get_footer();
