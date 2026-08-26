<?php
/**
 * 기술자료 공통 부품
 *
 * 이 화면의 목적은 하나입니다 — 설계사가 3초 안에 준불연 성적서를 받게 하는 것.
 * 그래서 목록에서 바로 파일이 열리고, 상세 페이지를 거치지 않아도 됩니다.
 *
 * ACF 함수는 쓰지 않고 get_post_meta 로만 읽습니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 첨부 파일 주소.
 * ACF 파일 필드는 설정에 따라 첨부물 번호 · 주소 · 배열 중 하나로 저장됩니다.
 * 셋 다 처리합니다.
 */
function caparol_doc_file( $post_id = 0 ) {

	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = get_post_meta( $post_id, 'file', true );

	$id  = 0;
	$url = '';

	if ( is_array( $raw ) ) {
		$id  = isset( $raw['ID'] ) ? (int) $raw['ID'] : 0;
		$url = isset( $raw['url'] ) ? (string) $raw['url'] : '';
	} elseif ( is_numeric( $raw ) ) {
		$id = (int) $raw;
	} elseif ( is_string( $raw ) && '' !== $raw ) {
		$url = $raw;
	}

	if ( $id && ! $url ) {
		$url = (string) wp_get_attachment_url( $id );
	}

	if ( ! $url ) {
		return array( 'url' => '', 'ext' => '', 'size' => '' );
	}

	// 확장자와 용량 — 목록에서 "이거 열어도 되나" 를 판단하게 해줍니다
	$ext  = strtoupper( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
	$size = '';

	if ( $id ) {
		$path = get_attached_file( $id );
		if ( $path && file_exists( $path ) ) {
			$size = size_format( (int) filesize( $path ), 1 );
		}
	}

	return array( 'url' => $url, 'ext' => $ext, 'size' => $size );
}

/**
 * 유효기간 판정.
 * 시험성적서가 만료된 채 걸려 있으면 설계사에게 신뢰를 잃습니다.
 * 그래서 화면에서 바로 보이게 합니다.
 *
 * @return array{state:string,label:string,date:string} state 는 ok | soon | expired | none
 */
function caparol_doc_expiry( $post_id = 0 ) {

	$post_id = $post_id ? $post_id : get_the_ID();
	$raw     = get_post_meta( $post_id, 'valid_until', true );

	if ( ! is_scalar( $raw ) || '' === trim( (string) $raw ) ) {
		return array( 'state' => 'none', 'label' => '', 'date' => '' );
	}

	$expires = strtotime( (string) $raw );
	if ( ! $expires ) {
		return array( 'state' => 'none', 'label' => '', 'date' => '' );
	}

	$date = date_i18n( 'Y-m-d', $expires );
	$days = (int) floor( ( $expires - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );

	if ( $days < 0 ) {
		return array( 'state' => 'expired', 'label' => '유효기간 만료', 'date' => $date );
	}
	if ( $days <= 60 ) {
		return array( 'state' => 'soon', 'label' => $days . '일 후 만료', 'date' => $date );
	}
	return array( 'state' => 'ok', 'label' => '유효기간 ' . $date, 'date' => $date );
}

/** 자료 한 줄. 루프 안에서 호출합니다. */
function caparol_document_row() {

	$id   = get_the_ID();
	$f    = caparol_doc_file( $id );
	$exp  = caparol_doc_expiry( $id );

	$note = cp_meta( 'doc_note', $id );

	$types = get_the_terms( $id, 'document_type' );
	$type  = ( $types && ! is_wp_error( $types ) ) ? $types[0]->name : '';

	// 파일이 있으면 바로 파일로, 없으면 자료 페이지로 보냅니다
	$href     = $f['url'] ? $f['url'] : get_permalink( $id );
	$external = (bool) $f['url'];

	// 부가 정보 한 줄 — 빈 값은 알아서 빠집니다
	$bits = array_filter( array( $type, $f['ext'], $f['size'] ) );
	?>
	<li class="dl-item<?php echo 'expired' === $exp['state'] ? ' is-expired' : ''; ?>">
		<a class="dl-item__link" href="<?php echo esc_url( $href ); ?>"
			<?php if ( $external ) : ?>target="_blank" rel="noopener"<?php endif; ?>>

			<span class="dl-item__icon" aria-hidden="true"><?php echo esc_html( $f['ext'] ? $f['ext'] : 'DOC' ); ?></span>

			<span class="dl-item__body">
				<span class="dl-item__title"><?php the_title(); ?></span>
				<?php if ( $note ) : ?>
					<span class="dl-item__note"><?php echo esc_html( $note ); ?></span>
				<?php endif; ?>
				<?php if ( $bits ) : ?>
					<span class="dl-item__meta"><?php echo esc_html( implode( '  ·  ', $bits ) ); ?></span>
				<?php endif; ?>
			</span>

			<span class="dl-item__right">
				<?php if ( 'none' !== $exp['state'] ) : ?>
					<span class="dl-tag dl-tag--<?php echo esc_attr( $exp['state'] ); ?>"><?php echo esc_html( $exp['label'] ); ?></span>
				<?php endif; ?>
				<span class="dl-item__action"><?php echo $external ? '다운로드' : '자세히'; ?></span>
			</span>

		</a>
	</li>
	<?php
}

/** 자료 구분 필터 */
function caparol_document_filter( $current = 0 ) {

	$terms = get_terms( array(
		'taxonomy'   => 'document_type',
		'hide_empty' => false,
		'parent'     => 0,
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$archive = get_post_type_archive_link( 'document' );
	?>
	<nav class="cl-filter" aria-label="자료 구분">
		<?php if ( $archive ) : ?>
			<a class="cl-chip<?php echo $current ? '' : ' is-active'; ?>" href="<?php echo esc_url( $archive ); ?>">전체</a>
		<?php endif; ?>
		<?php
		foreach ( $terms as $term ) {
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			printf(
				'<a class="cl-chip%1$s" href="%2$s">%3$s<span class="cl-chip__n">%4$d</span></a>',
				( (int) $term->term_id === (int) $current ) ? ' is-active' : '',
				esc_url( $link ),
				esc_html( $term->name ),
				(int) $term->count
			);
		}
		?>
	</nav>
	<?php
}
