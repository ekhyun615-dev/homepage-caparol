<?php
/**
 * 기술자료실 — /downloads
 *
 * 맨 위에 시험성적서·인증서를 따로 띄웁니다.
 * 설계사가 채택 여부를 결정하는 자료라, 목록 중간에 묻히면 안 됩니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="cp-page cl dl" id="cp-main">
	<div class="cl__in">

		<?php
		caparol_archive_head(
			'기술자료',
			'제품 데이터시트(TDS), 시공지침, 카탈로그, 시험성적서를 받아보실 수 있습니다.',
			(int) $GLOBALS['wp_query']->found_posts,
			'건'
		);
		?>

		<!-- ── 시험성적서 · 인증서 우선 노출 ─────────────── -->
		<?php
		$certs = new WP_Query( array(
			'post_type'           => 'document',
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'tax_query'           => array( array(
				'taxonomy' => 'document_type',
				'field'    => 'slug',
				'terms'    => 'certificate',
			) ),
		) );
		?>
		<?php if ( $certs->have_posts() ) : ?>
			<section class="dl-featured">
				<h2 class="dl-featured__title">시험성적서 · 인증서</h2>
				<p class="dl-featured__desc">준불연 성적서(KS F ISO 5660-1) 등 설계 검토에 필요한 서류입니다.</p>
				<ul class="dl-list">
					<?php while ( $certs->have_posts() ) { $certs->the_post(); caparol_document_row(); } ?>
				</ul>
			</section>
		<?php endif; wp_reset_postdata(); ?>

		<!-- ── 전체 자료 ─────────────────────────────────── -->
		<?php caparol_document_filter( 0 ); ?>

		<?php if ( have_posts() ) : ?>
			<ul class="dl-list dl-list--all">
				<?php while ( have_posts() ) { the_post(); caparol_document_row(); } ?>
			</ul>
			<?php caparol_archive_pagination(); ?>
		<?php else : ?>
			<?php caparol_archive_empty( '등록된 기술자료가 없습니다.' ); ?>
		<?php endif; ?>

		<p class="dl-note">
			필요한 자료가 없으신가요? <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">문의하기</a>로 요청하시면 담당자가 보내드립니다.
		</p>

	</div>
</main>

<?php
get_footer();
