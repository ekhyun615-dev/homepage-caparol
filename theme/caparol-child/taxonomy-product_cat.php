<?php
/**
 * 제품 카테고리 목록 — /products/category/paint 등
 *
 * archive-product.php 와 같은 부품을 씁니다.
 * 차이는 제목·설명이 카테고리에서 온다는 것뿐입니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
$name = ( $term && isset( $term->name ) ) ? $term->name : '제품';
$desc = ( $term && isset( $term->description ) ) ? trim( wp_strip_all_tags( $term->description ) ) : '';
$tid  = ( $term && isset( $term->term_id ) ) ? (int) $term->term_id : 0;
?>

<main class="cp-page cl" id="cp-main">
	<div class="cl__in">

		<?php
		caparol_archive_head( $name, $desc, (int) $GLOBALS['wp_query']->found_posts );
		caparol_product_filter( $tid );
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
			<?php caparol_archive_empty( '이 카테고리에는 아직 등록된 제품이 없습니다.' ); ?>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
