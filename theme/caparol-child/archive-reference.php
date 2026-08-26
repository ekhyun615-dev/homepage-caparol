<?php
/**
 * 시공사례 목록 — /references
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
			'시공사례',
			'국내외 현장에서 Caparol 제품이 어떻게 쓰였는지 확인하실 수 있습니다.',
			(int) $GLOBALS['wp_query']->found_posts
		);

		caparol_ref_filter( 0, 0 );
		?>

		<?php if ( have_posts() ) : ?>
			<div class="cl-grid">
				<?php while ( have_posts() ) { the_post(); caparol_reference_card(); } ?>
			</div>
			<?php caparol_archive_pagination(); ?>
		<?php else : ?>
			<?php caparol_archive_empty( '등록된 시공사례가 없습니다.' ); ?>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
