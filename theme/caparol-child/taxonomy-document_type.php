<?php
/**
 * 자료 구분별 목록 — /downloads/type/certificate 등
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$term = get_queried_object();
$name = ( $term && isset( $term->name ) ) ? $term->name : '기술자료';
$desc = ( $term && isset( $term->description ) ) ? trim( wp_strip_all_tags( $term->description ) ) : '';
$tid  = ( $term && isset( $term->term_id ) ) ? (int) $term->term_id : 0;
?>

<main class="cp-page cl dl" id="cp-main">
	<div class="cl__in">

		<?php
		caparol_archive_head( $name, $desc, (int) $GLOBALS['wp_query']->found_posts, '건' );
		caparol_document_filter( $tid );
		?>

		<?php if ( have_posts() ) : ?>
			<ul class="dl-list dl-list--all">
				<?php while ( have_posts() ) { the_post(); caparol_document_row(); } ?>
			</ul>
			<?php caparol_archive_pagination(); ?>
		<?php else : ?>
			<?php caparol_archive_empty( '이 구분에는 아직 등록된 자료가 없습니다.' ); ?>
		<?php endif; ?>

		<p class="dl-note">
			필요한 자료가 없으신가요? <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">문의하기</a>로 요청하시면 담당자가 보내드립니다.
		</p>

	</div>
</main>

<?php
get_footer();
