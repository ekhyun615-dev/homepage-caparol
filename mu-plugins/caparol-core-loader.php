<?php
/**
 * Plugin Name: Caparol Core Loader
 * Description: mu-plugins는 하위 폴더를 자동으로 읽지 않습니다. 이 파일이 caparol-core를 불러옵니다.
 *
 * 서버 배치:
 *   wp-content/mu-plugins/caparol-core-loader.php   ← 이 파일
 *   wp-content/mu-plugins/caparol-core/             ← 폴더 전체
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/caparol-core/caparol-core.php';
