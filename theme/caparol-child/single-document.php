<?php
/**
 * 기술자료 낱장 — /downloads/[자료명]
 *
 * 목록에서 파일로 바로 가므로 이 화면은 거의 안 보입니다.
 * 파일을 아직 안 올린 자료, 검색엔진 유입, 링크 공유용으로만 존재합니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$id   = get_the_ID();
	$f    = caparol_doc_file( $id );
	$exp  = caparol_doc_expiry( $id );
	$note = cp_meta( 'doc_note', $id );

	$types = get_the_terms( $id, 'document_type' );
	$type  = ( $types && ! is_wp_error( $types ) ) ? $types[0] : null;
	?>

	<main class="cp-page dl-single" id="cp-main">
		<div class="dl-single__in">

			<?php if ( $type ) :
				$tlink = get_term_link( $type ); ?>
				<p class="cp-eyebrow">
					<?php if ( is_wp_error( $tlink ) ) : ?>
						<?php echo esc_html( $type->name ); ?>
					<?php else : ?>
						<a href="<?php echo esc_url( $tlink ); ?>"><?php echo esc_html( $type->name ); ?></a>
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<h1 class="dl-single__title"><?php the_title(); ?></h1>

			<?php if ( $note ) : ?>
				<p class="dl-single__note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>

			<?php if ( 'none' !== $exp['state'] ) : ?>
				<p><span class="dl-tag dl-tag--<?php echo esc_attr( $exp['state'] ); ?>"><?php echo esc_html( $exp['label'] ); ?></span></p>
			<?php endif; ?>

			<p class="dl-single__actions">
				<?php if ( $f['url'] ) : ?>
					<a class="cp-btn cp-btn--primary cp-btn--lg" href="<?php echo esc_url( $f['url'] ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html( trim( '파일 받기 ' . trim( $f['ext'] . ' ' . $f['size'] ) ) ); ?>
					</a>
				<?php else : ?>
					<a class="cp-btn cp-btn--primary cp-btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">자료 요청하기</a>
				<?php endif; ?>
				<a class="cp-btn cp-btn--ghost cp-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'document' ) ); ?>">자료실 전체</a>
			</p>

			<?php if ( ! $f['url'] ) : ?>
				<p class="cp-note">이 자료는 아직 파일이 등록되지 않았습니다. 문의해 주시면 담당자가 직접 보내드립니다.</p>
			<?php endif; ?>

		</div>
	</main>

	<?php
endwhile;

get_footer();
