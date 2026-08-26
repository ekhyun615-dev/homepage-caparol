<?php
/**
 * 문의하기 — /contact
 *
 * 슬러그가 contact 인 페이지에 워드프레스가 자동으로 이 파일을 씁니다.
 * 페이지 본문에 무엇을 쓰든, 화면은 이 파일이 그립니다.
 * (본문에 내용을 넣으시면 폼 위에 함께 나옵니다)
 *
 * 폼 자체는 inc/contact-form.php 에 있습니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$i = caparol_info();
?>

<main class="cp-page cq-page" id="cp-main">
	<div class="cq-page__in">

		<header class="cl-head">
			<h1 class="cl-head__title">문의하기</h1>
			<p class="cl-head__desc">제품 견적, 샘플 신청, 기술 문의, 대리점 개설까지 — 무엇이든 문의해 주세요. 담당자가 직접 확인하고 답변드립니다.</p>
		</header>

		<!-- 빠른 연락 -->
		<div class="cq-quick">

			<?php if ( $i['tel'] ) : ?>
				<a class="cq-quick__item" href="tel:<?php echo esc_attr( $i['tel_raw'] ); ?>">
					<span class="cq-quick__label">전화 문의</span>
					<strong class="cq-quick__value"><?php echo esc_html( $i['tel'] ); ?></strong>
					<?php if ( $i['hours'] ) : ?>
						<span class="cq-quick__sub"><?php echo esc_html( $i['hours'] ); ?></span>
					<?php endif; ?>
				</a>
			<?php endif; ?>

			<?php if ( $i['email'] ) : ?>
				<a class="cq-quick__item" href="mailto:<?php echo esc_attr( $i['email'] ); ?>">
					<span class="cq-quick__label">이메일</span>
					<strong class="cq-quick__value"><?php echo esc_html( $i['email'] ); ?></strong>
					<span class="cq-quick__sub">자료 첨부가 필요할 때</span>
				</a>
			<?php endif; ?>

			<?php if ( $i['kakao'] ) : ?>
				<a class="cq-quick__item" href="<?php echo esc_url( $i['kakao'] ); ?>" target="_blank" rel="noopener">
					<span class="cq-quick__label">카카오톡</span>
					<strong class="cq-quick__value">카카오채널 상담</strong>
					<span class="cq-quick__sub">간단한 문의는 여기가 가장 빠릅니다</span>
				</a>
			<?php endif; ?>

		</div>

		<?php
		// 페이지 본문에 내용을 쓰셨으면 폼 위에 보여줍니다
		while ( have_posts() ) :
			the_post();
			$body = trim( wp_strip_all_tags( get_the_content() ) );
			if ( '' !== $body ) {
				echo '<div class="cq-intro cp-prose">' . apply_filters( 'the_content', get_the_content() ) . '</div>';
			}
		endwhile;
		?>

		<?php caparol_contact_form(); ?>

		<!-- 오시는 길 · 사업자 정보 -->
		<?php if ( $i['address'] ) : ?>
			<section class="cq-visit">
				<h2 class="cq__title">오시는 길</h2>
				<p class="cq-visit__addr"><?php echo esc_html( $i['address'] ); ?></p>
				<p class="cq-visit__links">
					<a class="cp-btn cp-btn--ghost" target="_blank" rel="noopener"
					   href="https://map.naver.com/p/search/<?php echo rawurlencode( $i['address'] ); ?>">네이버 지도에서 보기</a>
					<a class="cp-btn cp-btn--ghost" target="_blank" rel="noopener"
					   href="https://map.kakao.com/?q=<?php echo rawurlencode( $i['address'] ); ?>">카카오맵에서 보기</a>
				</p>
			</section>
		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
