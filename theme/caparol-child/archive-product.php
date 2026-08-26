<?php
/**
 * 제품 목록 — /products
 *
 * 카드 디자인·필터는 inc/archive-parts.php 에 있습니다.
 * 이 파일은 뼈대만 담당합니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="cp-page cl" id="cp-main">
	<div class="cl__in">

		<?php
		caparol_archive_head(
			'제품',
			'독일 Caparol 의 프라이머부터 외단열시스템까지, 카파롤코리아가 직접 수입·공급하는 전 제품입니다.',
			(int) $GLOBALS['wp_query']->found_posts
		);

		caparol_product_filter( 0 );
		?>

		<?php if ( have_posts() ) : ?>

			<div class="cl-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					caparol_product_card();
				}
				?>
			</div>

			<?php caparol_archive_pagination(); ?>

		<?php else : ?>
			<?php caparol_archive_empty(); ?>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
