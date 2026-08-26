<?php
/**
 * Plugin Name: Caparol Core
 * Description: caparol.kr 사이트의 콘텐츠 구조(제품·시공사례·색상·기술자료)와 기본 보안 설정.
 * Version:     0.1.0
 * Author:      Caparol Korea
 *
 * ┌─────────────────────────────────────────────────────────────┐
 * │ 이 파일은 mu-plugins(must-use plugin)로 동작합니다.            │
 * │ 서버 경로: wp-content/mu-plugins/                            │
 * │                                                             │
 * │ 왜 테마가 아니라 mu-plugin인가:                                │
 * │  - 테마를 바꿔도 제품·시공사례 데이터가 살아남습니다.             │
 * │  - 비활성화가 불가능해서 실수로 꺼질 일이 없습니다.               │
 * │  - 콘텐츠 구조는 "디자인"이 아니라 "사이트의 뼈대"입니다.          │
 * └─────────────────────────────────────────────────────────────┘
 *
 * 설치: wp-content/mu-plugins/ 안에 caparol-core.php(로더)와 caparol-core/ 폴더를 둡니다.
 *       mu-plugins는 하위 폴더를 자동 로드하지 않으므로 아래 로더가 필요합니다.
 *       → wp-content/mu-plugins/caparol-core-loader.php 참고
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAPAROL_CORE_DIR', __DIR__ );

require_once CAPAROL_CORE_DIR . '/inc/post-types.php';
require_once CAPAROL_CORE_DIR . '/inc/taxonomies.php';
require_once CAPAROL_CORE_DIR . '/inc/seed-products.php';
require_once CAPAROL_CORE_DIR . '/inc/security.php';

/**
 * 콘텐츠 구조가 바뀌면 고유주소를 갱신해야 404가 나지 않습니다.
 * 이 플러그인 파일을 수정한 뒤 [설정 → 고유주소]에 한 번 들어가 저장하세요.
 */
