<?php
/**
 * 문의 폼
 *
 * Contact Form 7 같은 플러그인을 쓰지 않고 직접 처리합니다. 이유는 두 가지입니다.
 *
 * 1) 접수 내용을 데이터베이스에 저장합니다.
 *    메일만 보내는 방식은 발송이 실패하거나 스팸함에 빠지면 문의가 그냥 사라집니다.
 *    여기서는 관리자 → "문의 접수" 에 항상 남습니다.
 * 2) 화면을 코드로 그리므로 디자인이 사이트와 어긋나지 않습니다.
 *
 * 스팸 대책 (3중)
 *   - 꿀단지(honeypot): 사람 눈에 안 보이는 칸. 봇이 채우면 버립니다.
 *   - 시간 검사: 폼을 연 지 3초 안에 제출되면 사람이 아닙니다.
 *   - 논스(nonce): 다른 사이트에서 몰래 제출하는 것을 막습니다.
 *   - 같은 IP에서 1시간에 5건까지만 받습니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 문의 유형 — 값 => 화면에 보이는 이름 */
function caparol_inquiry_types() {
	return array(
		'quote'  => '견적 문의',
		'sample' => '샘플 · 색상집 신청',
		'tech'   => '기술 문의',
		'etc'    => '기타',
	);
}

/**
 * 관리자 목록에 유형 이름을 보여줄 때 씁니다.
 * 나중에 유형을 빼더라도 이미 접수된 문의가 "—" 로 보이지 않도록,
 * 지난 유형 이름을 여기서 함께 찾습니다.
 */
function caparol_inquiry_type_label( $key ) {

	$types = caparol_inquiry_types();
	if ( isset( $types[ $key ] ) ) {
		return $types[ $key ];
	}

	// 지금은 쓰지 않는 유형 — 과거 접수분 표시용
	$retired = array(
		'partner' => '대리점 문의 (종료)',
	);

	return isset( $retired[ $key ] ) ? $retired[ $key ] : '—';
}

/**
 * 폼 처리 중 생긴 오류와 사용자가 입력한 값을 담아둡니다.
 * 제출 처리(template_redirect)와 화면 그리기가 서로 다른 시점에 일어나므로
 * 값을 여기에 잠시 보관합니다.
 */
$GLOBALS['caparol_form'] = array( 'errors' => array(), 'old' => array() );

function caparol_form_error( $field ) {
	return isset( $GLOBALS['caparol_form']['errors'][ $field ] )
		? $GLOBALS['caparol_form']['errors'][ $field ]
		: '';
}

function caparol_form_old( $field ) {
	return isset( $GLOBALS['caparol_form']['old'][ $field ] )
		? $GLOBALS['caparol_form']['old'][ $field ]
		: '';
}

function caparol_form_has_errors() {
	return ! empty( $GLOBALS['caparol_form']['errors'] );
}

/* ──────────────────────────────────────────────
   제출 처리 — 화면이 그려지기 전에 실행됩니다
   ────────────────────────────────────────────── */
add_action( 'template_redirect', 'caparol_handle_contact_submit' );

function caparol_handle_contact_submit() {

	if ( empty( $_POST['caparol_contact'] ) ) {
		return;
	}

	// 논스 확인 — 실패하면 폼을 다시 열어야 하는 상황(캐시·세션만료)이므로 안내만 합니다
	if ( ! isset( $_POST['caparol_nonce'] ) || ! wp_verify_nonce( $_POST['caparol_nonce'], 'caparol_contact' ) ) {
		$GLOBALS['caparol_form']['errors']['_form'] = '보안 확인에 실패했습니다. 페이지를 새로 고친 뒤 다시 시도해 주세요.';
		return;
	}

	// 꿀단지 — 봇만 채우는 칸
	if ( ! empty( $_POST['caparol_website'] ) ) {
		return;   // 조용히 버립니다. 봇에게 실패를 알려줄 필요가 없습니다.
	}

	// 시간 검사
	$opened = isset( $_POST['caparol_t'] ) ? (int) $_POST['caparol_t'] : 0;
	if ( $opened && ( time() - $opened ) < 3 ) {
		return;
	}

	// 같은 IP 제한 — 1시간에 5건
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'caparol_rate_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 5 ) {
		$GLOBALS['caparol_form']['errors']['_form'] = '짧은 시간에 너무 많이 보내셨습니다. 잠시 후 다시 시도하거나 전화로 문의해 주세요.';
		return;
	}

	// ── 입력값 정리 ──────────────────────────────────
	$in = array(
		'type'    => isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '',
		'name'    => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
		'company' => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '',
		'phone'   => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
		'email'   => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'site'    => isset( $_POST['site'] ) ? sanitize_text_field( wp_unslash( $_POST['site'] ) ) : '',
		'message' => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
		'agree'   => ! empty( $_POST['agree'] ),
	);
	$GLOBALS['caparol_form']['old'] = $in;

	// ── 검증 ─────────────────────────────────────────
	$types = caparol_inquiry_types();

	if ( ! isset( $types[ $in['type'] ] ) ) {
		$GLOBALS['caparol_form']['errors']['type'] = '문의 유형을 선택해 주세요.';
	}
	if ( '' === $in['name'] ) {
		$GLOBALS['caparol_form']['errors']['name'] = '이름을 입력해 주세요.';
	}
	if ( '' === $in['phone'] ) {
		$GLOBALS['caparol_form']['errors']['phone'] = '연락처를 입력해 주세요.';
	}
	if ( '' === $in['email'] || ! is_email( $in['email'] ) ) {
		$GLOBALS['caparol_form']['errors']['email'] = '이메일 주소를 정확히 입력해 주세요.';
	}
	if ( mb_strlen( $in['message'] ) < 5 ) {
		$GLOBALS['caparol_form']['errors']['message'] = '문의 내용을 입력해 주세요.';
	}
	if ( ! $in['agree'] ) {
		$GLOBALS['caparol_form']['errors']['agree'] = '개인정보 수집·이용에 동의해 주셔야 접수가 가능합니다.';
	}

	if ( caparol_form_has_errors() ) {
		return;   // 폼을 다시 그리면서 오류를 표시합니다
	}

	// ── 저장 ─────────────────────────────────────────
	$label = $types[ $in['type'] ];

	$body = "문의 유형 : {$label}\n"
		. "이름     : {$in['name']}\n"
		. ( $in['company'] ? "회사·소속 : {$in['company']}\n" : '' )
		. "연락처   : {$in['phone']}\n"
		. "이메일   : {$in['email']}\n"
		. ( $in['site'] ? "현장 위치 : {$in['site']}\n" : '' )
		. "\n― 문의 내용 ―\n" . $in['message'];

	$post_id = wp_insert_post( array(
		'post_type'    => 'inquiry',
		'post_status'  => 'publish',
		'post_title'   => '[' . $label . '] ' . $in['name'] . ' — ' . wp_date( 'Y-m-d H:i' ),
		'post_content' => $body,
	), true );

	if ( ! is_wp_error( $post_id ) ) {
		foreach ( $in as $k => $v ) {
			update_post_meta( $post_id, 'cq_' . $k, is_bool( $v ) ? ( $v ? '1' : '' ) : $v );
		}
		update_post_meta( $post_id, 'cq_ip', $ip );
	}

	// ── 메일 발송 ────────────────────────────────────
	$to = caparol_info( 'inquiry_to' );
	if ( ! $to ) {
		$to = get_option( 'admin_email' );
	}

	wp_mail(
		$to,
		'[홈페이지 문의] ' . $label . ' — ' . $in['name'],
		$body . "\n\n관리자에서 보기: " . admin_url( 'edit.php?post_type=inquiry' ),
		array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $in['name'] . ' <' . $in['email'] . '>',
		)
	);

	set_transient( $key, $hits + 1, HOUR_IN_SECONDS );

	// 새로고침으로 같은 문의가 두 번 접수되지 않도록 주소를 바꿔 이동합니다
	wp_safe_redirect( add_query_arg( 'sent', '1', get_permalink() ) . '#contact-form' );
	exit;
}

/* ──────────────────────────────────────────────
   폼 그리기
   ────────────────────────────────────────────── */

/** 입력칸 하나 */
function caparol_field( $name, $label, $args = array() ) {

	$a = wp_parse_args( $args, array(
		'type'        => 'text',
		'required'    => false,
		'placeholder' => '',
		'hint'        => '',
		'autocomplete'=> '',
	) );

	$err = caparol_form_error( $name );
	$id  = 'cq-' . $name;
	?>
	<p class="cq-field<?php echo $err ? ' has-error' : ''; ?>">
		<label class="cq-label" for="<?php echo esc_attr( $id ); ?>">
			<?php echo esc_html( $label ); ?>
			<?php if ( $a['required'] ) : ?><span class="cq-req" aria-hidden="true">*</span><?php endif; ?>
		</label>

		<?php if ( 'textarea' === $a['type'] ) : ?>
			<textarea class="cq-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"
				rows="7" placeholder="<?php echo esc_attr( $a['placeholder'] ); ?>"
				<?php echo $a['required'] ? 'required' : ''; ?>><?php echo esc_textarea( caparol_form_old( $name ) ); ?></textarea>
		<?php else : ?>
			<input class="cq-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"
				type="<?php echo esc_attr( $a['type'] ); ?>"
				value="<?php echo esc_attr( caparol_form_old( $name ) ); ?>"
				placeholder="<?php echo esc_attr( $a['placeholder'] ); ?>"
				<?php echo $a['autocomplete'] ? 'autocomplete="' . esc_attr( $a['autocomplete'] ) . '"' : ''; ?>
				<?php echo $a['required'] ? 'required' : ''; ?>>
		<?php endif; ?>

		<?php if ( $a['hint'] && ! $err ) : ?>
			<span class="cq-hint"><?php echo esc_html( $a['hint'] ); ?></span>
		<?php endif; ?>
		<?php if ( $err ) : ?>
			<span class="cq-error"><?php echo esc_html( $err ); ?></span>
		<?php endif; ?>
	</p>
	<?php
}

/** 폼 전체 */
function caparol_contact_form() {

	$sent  = ! empty( $_GET['sent'] );
	$types = caparol_inquiry_types();
	$fatal = caparol_form_error( '_form' );
	$has   = caparol_form_has_errors();
	?>
	<section class="cq" id="contact-form">

		<?php if ( $sent ) : ?>
			<div class="cq-done" role="status">
				<h2 class="cq-done__title">문의가 접수되었습니다</h2>
				<p class="cq-done__text">
					영업일 기준 1~2일 이내에 담당자가 연락드리겠습니다.<br>
					급하신 경우 <a href="tel:<?php echo esc_attr( caparol_info( 'tel_raw' ) ); ?>"><?php echo esc_html( caparol_info( 'tel' ) ); ?></a> 로 전화 주세요.
				</p>
			</div>
		<?php endif; ?>

		<h2 class="cq__title">온라인 문의</h2>
		<p class="cq__lead">아래 내용을 남겨주시면 담당자가 확인 후 연락드립니다. <span class="cq-req">*</span> 표시는 필수 항목입니다.</p>

		<?php if ( $fatal ) : ?>
			<p class="cq-alert" role="alert"><?php echo esc_html( $fatal ); ?></p>
		<?php elseif ( $has ) : ?>
			<p class="cq-alert" role="alert">입력하지 않았거나 잘못된 항목이 있습니다. 아래 붉은색 안내를 확인해 주세요.</p>
		<?php endif; ?>

		<form class="cq-form" method="post" action="<?php echo esc_url( get_permalink() ); ?>#contact-form" novalidate>

			<?php wp_nonce_field( 'caparol_contact', 'caparol_nonce' ); ?>
			<input type="hidden" name="caparol_contact" value="1">
			<input type="hidden" name="caparol_t" value="<?php echo esc_attr( time() ); ?>">

			<!-- 사람에게는 보이지 않는 칸입니다. 비워두세요. -->
			<div class="cq-hp" aria-hidden="true">
				<label>홈페이지 주소<input type="text" name="caparol_website" tabindex="-1" autocomplete="off"></label>
			</div>

			<!-- 문의 유형 -->
			<?php $type_err = caparol_form_error( 'type' ); ?>
			<fieldset class="cq-field cq-field--full<?php echo $type_err ? ' has-error' : ''; ?>">
				<legend class="cq-label">문의 유형 <span class="cq-req" aria-hidden="true">*</span></legend>
				<div class="cq-types">
					<?php foreach ( $types as $value => $label ) : ?>
						<label class="cq-type">
							<input type="radio" name="type" value="<?php echo esc_attr( $value ); ?>"
								<?php checked( caparol_form_old( 'type' ), $value ); ?>>
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
				<?php if ( $type_err ) : ?>
					<span class="cq-error"><?php echo esc_html( $type_err ); ?></span>
				<?php endif; ?>
			</fieldset>

			<div class="cq-row">
				<?php
				caparol_field( 'name', '이름', array( 'required' => true, 'autocomplete' => 'name' ) );
				caparol_field( 'company', '회사 · 소속', array( 'placeholder' => '개인이시면 비워두셔도 됩니다', 'autocomplete' => 'organization' ) );
				caparol_field( 'phone', '연락처', array( 'required' => true, 'type' => 'tel', 'placeholder' => '010-0000-0000', 'autocomplete' => 'tel' ) );
				caparol_field( 'email', '이메일', array( 'required' => true, 'type' => 'email', 'autocomplete' => 'email' ) );
				?>
			</div>

			<?php
			caparol_field( 'site', '현장 위치', array(
				'placeholder' => '예) 경기도 성남시 분당구',
				'hint'        => '견적 문의는 현장 위치를 남겨주시면 더 빠르게 안내해 드립니다.',
			) );

			caparol_field( 'message', '문의 내용', array(
				'required'    => true,
				'type'        => 'textarea',
				'placeholder' => "제품명, 시공 면적, 시공 예정일 등을 함께 적어주시면 더 정확하게 안내해 드립니다.",
			) );
			?>

			<!-- 동의 -->
			<?php $agree_err = caparol_form_error( 'agree' ); ?>
			<div class="cq-field cq-field--full<?php echo $agree_err ? ' has-error' : ''; ?>">
				<label class="cq-agree">
					<input type="checkbox" name="agree" value="1" <?php checked( (bool) caparol_form_old( 'agree' ) ); ?>>
					<span>
						개인정보 수집·이용에 동의합니다. <span class="cq-req" aria-hidden="true">*</span>
						<small>수집 항목: 이름, 연락처, 이메일, 회사명, 현장 위치 · 이용 목적: 문의 답변 및 상담 · 보유 기간: 문의 처리 완료 후 3년</small>
					</span>
				</label>
				<?php if ( $agree_err ) : ?>
					<span class="cq-error"><?php echo esc_html( $agree_err ); ?></span>
				<?php endif; ?>
			</div>

			<p class="cq-submit">
				<button type="submit" class="cp-btn cp-btn--primary cp-btn--lg">문의 보내기</button>
			</p>

		</form>
	</section>
	<?php
}

/* ──────────────────────────────────────────────
   관리자 화면 — 문의 접수 목록

   목록에서 유형·이름·연락처가 바로 보이게 합니다.
   글을 하나씩 열어보지 않아도 되도록.
   ────────────────────────────────────────────── */

add_filter( 'manage_inquiry_posts_columns', function ( $columns ) {
	return array(
		'cb'        => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'     => '문의',
		'cq_type'   => '유형',
		'cq_name'   => '이름',
		'cq_phone'  => '연락처',
		'cq_email'  => '이메일',
		'date'      => '접수일',
	);
} );

add_action( 'manage_inquiry_posts_custom_column', function ( $column, $post_id ) {

	if ( 'cq_type' === $column ) {
		$key = get_post_meta( $post_id, 'cq_type', true );
		echo esc_html( caparol_inquiry_type_label( is_scalar( $key ) ? (string) $key : '' ) );
		return;
	}

	$map = array( 'cq_name' => 'cq_name', 'cq_phone' => 'cq_phone', 'cq_email' => 'cq_email' );
	if ( ! isset( $map[ $column ] ) ) {
		return;
	}

	$value = get_post_meta( $post_id, $map[ $column ], true );
	if ( ! is_scalar( $value ) || '' === $value ) {
		echo '—';
		return;
	}

	if ( 'cq_email' === $column ) {
		printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $value ) );
	} elseif ( 'cq_phone' === $column ) {
		printf( '<a href="tel:%1$s">%2$s</a>', esc_attr( preg_replace( '/[^0-9+]/', '', $value ) ), esc_html( $value ) );
	} else {
		echo esc_html( $value );
	}
}, 10, 2 );

/* 문의가 들어오면 관리자 메뉴에 개수를 표시합니다 — 놓치지 않도록 */
add_filter( 'display_post_states', function ( $states, $post ) {
	if ( 'inquiry' === $post->post_type && ! get_post_meta( $post->ID, 'cq_read', true ) ) {
		$states['cq_new'] = '<span style="color:#5f7c0b;font-weight:700">신규</span>';
	}
	return $states;
}, 10, 2 );

/* 문의를 한 번 열어보면 '신규' 표시를 뗍니다 */
add_action( 'load-post.php', function () {
	$id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	if ( $id && 'inquiry' === get_post_type( $id ) ) {
		update_post_meta( $id, 'cq_read', '1' );
	}
} );
