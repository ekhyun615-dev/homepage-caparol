<?php
/**
 * 푸터
 *
 * Astra 기본 푸터는 CSS로 숨기고(style.css) 이 파일이 대신 그립니다.
 * 커스터마이저에서 푸터를 조립할 필요가 없어, 화면이 흐트러질 일이 없습니다.
 *
 * 배경은 밝은 회색입니다.
 * 지금 가진 로고는 코끼리가 검정이라 어두운 배경에서 보이지 않기 때문입니다.
 * 본사에서 진짜 반전 로고(코끼리까지 흰색)를 받으면
 * style.css 의 --cf-bg / --cf-text 두 값만 바꾸면 검정 푸터가 됩니다.
 *
 * 회사 정보는 inc/site-info.php 한 곳에서 옵니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 링크 목록을 그립니다. 현재 보고 있는 페이지는 표시하지 않습니다(단순하게 유지). */
function caparol_footer_links( $items ) {
	$out = '<ul class="cf__list">';
	foreach ( $items as $label => $path ) {
		$out .= '<li><a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $label ) . '</a></li>';
	}
	return $out . '</ul>';
}

function caparol_render_footer() {

	$i = caparol_info();
	?>
	<footer class="cf" role="contentinfo">
		<div class="cf__in">

			<!-- 브랜드 -->
			<div class="cf__brand">
				<?php
				if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
					echo get_custom_logo();
				} else {
					echo '<p class="cf__name">' . esc_html( get_bloginfo( 'name' ) ) . '</p>';
				}
				?>
				<p class="cf__tagline"><?php echo esc_html( $i['tagline'] ); ?></p>
			</div>

			<!-- 제품 -->
			<nav class="cf__col" aria-label="제품">
				<h2 class="cf__heading">제품</h2>
				<?php
				echo caparol_footer_links( array(
					'프라이머'           => '/products/category/primer/',
					'페인트'             => '/products/category/paint/',
					'플라스터 / 퍼티'    => '/products/category/plaster/',
					'유리직물벽지'       => '/products/category/capaver/',
					'장식 인테리어'      => '/products/category/decor/',
					'외단열시스템'       => '/products/category/eifs/',
					'Meldorfer 벽돌마감' => '/products/category/meldorfer/',
				) );
				?>
			</nav>

			<!-- 회사 -->
			<nav class="cf__col" aria-label="회사">
				<h2 class="cf__heading">회사</h2>
				<?php
				echo caparol_footer_links( array(
					'브랜드 소개'  => '/brand/',
					'시공사례'     => '/references/',
					'기술자료실'   => '/downloads/',
					'색상'         => '/colors/',
				) );
				?>
			</nav>

			<!-- 연락처 -->
			<div class="cf__col cf__contact">
				<h2 class="cf__heading">문의</h2>

				<?php if ( $i['tel'] ) : ?>
					<a class="cf__tel" href="tel:<?php echo esc_attr( $i['tel_raw'] ); ?>"><?php echo esc_html( $i['tel'] ); ?></a>
				<?php endif; ?>

				<?php if ( $i['hours'] ) : ?>
					<p class="cf__hours"><?php echo esc_html( $i['hours'] ); ?></p>
				<?php endif; ?>

				<?php if ( $i['email'] ) : ?>
					<p class="cf__email"><a href="mailto:<?php echo esc_attr( $i['email'] ); ?>"><?php echo esc_html( $i['email'] ); ?></a></p>
				<?php endif; ?>

				<a class="cp-btn cp-btn--primary cf__cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">온라인 문의</a>
			</div>

		</div>

		<!-- 하단 법적 표기 -->
		<div class="cf__legal">
			<div class="cf__legal-in">
				<p class="cf__biz">
					<?php
					$bits = array();
					if ( $i['company'] ) { $bits[] = '상호 ' . $i['company']; }
					if ( $i['ceo'] )     { $bits[] = '대표 ' . $i['ceo']; }
					if ( $i['biz_no'] )  { $bits[] = '사업자등록번호 ' . $i['biz_no']; }
					if ( $i['address'] ) { $bits[] = $i['address']; }
					echo esc_html( implode( '  ·  ', $bits ) );
					?>
				</p>
				<p class="cf__copy">
					<a class="cf__privacy" href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">개인정보처리방침</a>
					<span class="cf__sep">·</span>
					&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $i['company'] ); ?>. All rights reserved.
				</p>
			</div>
		</div>
	</footer>
	<?php
}

// 워드프레스의 마지막 훅에서 출력합니다. 테마 버전이 바뀌어도 안전합니다.
add_action( 'wp_footer', 'caparol_render_footer', 5 );
