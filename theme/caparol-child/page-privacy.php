<?php
/**
 * 개인정보처리방침 — /privacy
 *
 * 슬러그가 privacy 인 페이지에 워드프레스가 자동으로 이 파일을 씁니다.
 * 본문(법적 문구)은 워드프레스 편집기에서 자유롭게 고치실 수 있습니다.
 * 이 파일은 읽기 좋은 여백과 글자 크기만 담당합니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main class="cp-page pv" id="cp-main">
		<div class="pv__in">

			<header class="pv-head">
				<h1 class="pv-head__title"><?php the_title(); ?></h1>
				<p class="pv-head__date">시행일 <?php echo esc_html( get_the_modified_date( 'Y년 n월 j일' ) ); ?></p>
			</header>

			<div class="pv-body cp-prose">
				<?php the_content(); ?>
			</div>

		</div>
	</main>
	<?php
endwhile;

get_footer();
