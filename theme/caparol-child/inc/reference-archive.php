<?php
/**
 * 시공사례 분류별 목록의 알맹이.
 * taxonomy-reference_region.php / taxonomy-reference_type.php 가 공통으로 씁니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function caparol_render_reference_taxonomy() {

	$term = get_queried_object();
	$name = ( $term && isset( $term->name ) ) ? $term->name : '시공사례';
	$desc = ( $term && isset( $term->description ) ) ? trim( wp_strip_all_tags( $term->description ) ) : '';
	$tid  = ( $term && isset( $term->term_id ) ) ? (int) $term->term_id : 0;
	$tax  = ( $term && isset( $term->taxonomy ) ) ? $term->taxonomy : '';

	$region = ( 'reference_region' === $tax ) ? $tid : 0;
	$type   = ( 'reference_type' === $tax ) ? $tid : 0;
	?>
	<main class="cp-page cl" id="cp-main">
		<div class="cl__in">

			<?php
			caparol_archive_head( $name, $desc, (int) $GLOBALS['wp_query']->found_posts );
			caparol_ref_filter( $region, $type );
			?>

			<?php if ( have_posts() ) : ?>
				<div class="cl-grid">
					<?php while ( have_posts() ) { the_post(); caparol_reference_card(); } ?>
				</div>
				<?php caparol_archive_pagination(); ?>
			<?php else : ?>
				<?php caparol_archive_empty( '이 분류에는 아직 등록된 시공사례가 없습니다.' ); ?>
			<?php endif; ?>

		</div>
	</main>
	<?php
}
