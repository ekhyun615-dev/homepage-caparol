<?php
/**
 * 기본 보안 · 정리
 *
 * 플러그인으로 해결할 부분(방화벽·스캔)은 Wordfence가 맡고,
 * 여기서는 코드로 끄는 게 확실한 것들만 처리합니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* 워드프레스 버전 노출 제거 — 취약 버전을 광고하지 않기 위해 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* XML-RPC 비활성화 — 무차별 로그인 시도의 주요 통로 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/* 로그인 실패 사유를 숨김 — "비밀번호가 틀렸습니다"는 아이디가 맞다는 뜻 */
add_filter( 'login_errors', function () {
	return '아이디 또는 비밀번호가 올바르지 않습니다.';
} );

/* REST API로 사용자 목록이 새어나가는 것 차단 (비로그인 상태) */
add_filter( 'rest_endpoints', function ( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	return $endpoints;
} );

/* 작성자 아카이브(/?author=1)로 관리자 아이디가 노출되는 것 차단 */
add_action( 'template_redirect', function () {
	if ( is_author() ) {
		wp_safe_redirect( home_url(), 301 );
		exit;
	}
} );

/**
 * 이미지 업로드 시 원본이 너무 크면 서버와 속도에 부담이 됩니다.
 * 직원이 폰 사진을 그대로 올리는 상황을 대비해 상한을 둡니다.
 */
add_filter( 'big_image_size_threshold', function () {
	return 2000; // px
} );

/**
 * 한글 파일명은 서버·브라우저 환경에 따라 깨집니다.
 * 업로드 시 파일명을 안전한 형태로 자동 변환합니다.
 */
add_filter( 'sanitize_file_name', function ( $filename ) {
	if ( preg_match( '/[^\x20-\x7e]/', $filename ) ) {
		$ext  = pathinfo( $filename, PATHINFO_EXTENSION );
		$name = 'caparol-' . date( 'Ymd' ) . '-' . substr( md5( $filename . microtime() ), 0, 8 );
		return $ext ? $name . '.' . strtolower( $ext ) : $name;
	}
	return $filename;
}, 10, 1 );
