<?php
/**
 * 제품 상세 페이지 — 최소 버전
 *
 * 전체 버전(single-product-full.php.txt)에서 치명적 오류가 나서,
 * 확실히 동작하는 최소 구성으로 되돌린 상태입니다.
 * 오류 원인이 확인되면 섹션을 하나씩 다시 붙입니다.
 *
 * 여기서는 워드프레스 기본 함수와, 이미 검증된 자식 테마 헬퍼만 씁니다.
 * ACF를 직접 호출하지 않으므로 필드가 비어 있어도 안전합니다.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="cp-product">

	<?php while ( have_posts() ) : the_post(); ?>

		<h1 class="cp-product__title"><?php the_title(); ?></h1>

		<?php the_content(); ?>

		<?php
		if ( function_exists( 'caparol_features_list' ) ) {
			$cp_features = caparol_features_list();
			if ( $cp_features ) {
				echo '<h2 class="cp-section__title">주요 특징</h2>';
				echo $cp_features; // 내부에서 이스케이프 처리됨
			}
		}
		?>

		<?php
		if ( function_exists( 'caparol_specs_table' ) ) {
			$cp_specs = caparol_specs_table();
			if ( $cp_specs ) {
				echo '<h2 class="cp-section__title">기술 데이터</h2>';
				echo $cp_specs;
			}
		}
		?>

		<p class="cp-product__actions">
			<a class="cp-btn cp-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">견적 문의</a>
		</p>

	<?php endwhile; ?>

</main>

<?php
get_footer();
