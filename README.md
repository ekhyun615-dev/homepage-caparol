# Caparol Korea 홈페이지 (caparol.kr)

독일 **Caparol**(1895년 창립, DAW 그룹) 건축용 도료·외단열시스템의 한국 공식 파트너 홈페이지 프로젝트입니다.

---

## ⚠️ 이 저장소의 역할

이 저장소는 **사이트 백업이 아닙니다.** caparol.kr은 워드프레스로 구축하므로,
사이트의 실제 콘텐츠는 서버의 DB와 `uploads/` 폴더에 있습니다.

| 대상 | 어디에 사는가 | 이 저장소에 넣나 |
|---|---|---|
| 제품·시공사례·공지 글 | 워드프레스 DB | ✗ — UpdraftPlus 백업 |
| 업로드한 사진·PDF | `wp-content/uploads/` | ✗ — UpdraftPlus 백업 |
| 워드프레스 본체, 남의 플러그인/테마 | 서버 | ✗ — 자동 업데이트 |
| **콘텐츠 구조 코드** | `wp-content/mu-plugins/` | ✓ `mu-plugins/` |
| **자식 테마 코드** | `wp-content/themes/caparol-child/` | ✓ `theme/` |
| **설계서·세팅·운영 문서** | — | ✓ `docs/` |
| **로고·브랜드 원본** | — | ✓ `brand/` |
| `wp-config.php` (DB 비밀번호) | 서버 | ✗ **절대 금지** |

> 이 구분이 흐려지면 사고가 납니다. 사이트 복구는 Git이 아니라 **백업 플러그인**이 담당합니다.

## 🗂 문서

| 문서 | 내용 | 언제 보나 |
|---|---|---|
| [docs/getting-started.md](docs/getting-started.md) | **워드프레스 처음 시작하기** — 설치부터 첫 페이지까지 따라하기 | 맨 처음 |
| [docs/ia.md](docs/ia.md) | 사이트 구조 설계서 — 메뉴·페이지·제품 분류·페이지별 구성 | **워드프레스 만지기 전에 먼저** |
| [docs/wordpress-setup.md](docs/wordpress-setup.md) | 테마·플러그인·보안·SEO 초기 세팅 순서 | 구축 시작할 때 |
| [docs/product-template.md](docs/product-template.md) | 제품 상세 템플릿 만들기 (Elementor 테마 빌더) | 필드 만든 다음 |
| [docs/header-template.md](docs/header-template.md) | 헤더·메뉴 만들기 (Elementor) | 제품 템플릿 다음 |
| [docs/acf-fields.md](docs/acf-fields.md) | 제품·색상·시공사례·기술자료 입력 칸 설정 (필드 이름 정확히) | 구조 만든 다음 |
| [docs/content-plan.md](docs/content-plan.md) | 확보해야 할 자료 목록 (사진·PDF·회사정보) | 자료 수집할 때 |
| [docs/operations.md](docs/operations.md) | 백업 주기·업데이트 규칙·장애 대응 | 오픈 후 상시 |

## 📦 코드

| 폴더 | 내용 | 서버 배치 위치 |
|---|---|---|
| [mu-plugins/](mu-plugins/) | 제품·시공사례·색상·기술자료 구조 + 기본 보안 | `wp-content/mu-plugins/` |
| [theme/caparol-child/](theme/) | 디자인, 모바일 고정 바, ACF 필드 정의(JSON) | `wp-content/themes/caparol-child/` |

> **구조는 mu-plugin, 디자인은 테마.** 테마를 바꿔도 제품 데이터가 남도록 분리했습니다.

## 🛠 기술 스택

| 항목 | 결정 | 이유 |
|---|---|---|
| 플랫폼 | **WordPress** | 직원이 직접·자주 콘텐츠를 수정하고, 게시판/자료실이 필요함 |
| 호스팅 | PHP 8.x + MySQL 지원 요금제 (카페24 등) | 정적 요금제로는 불가 |
| 테마 | **Astra** + 자식 테마 `caparol-child` | 가볍고 Elementor와 궁합이 좋음 |
| 페이지 빌더 | **Elementor Pro** | 제품 템플릿(CPT)과 메가메뉴에 Pro가 필요 |
| 개발 환경 | [LocalWP](https://localwp.com) (로컬 워드프레스) | 실서버 건드리지 않고 실험 |

> 자매 사이트 [homepage-creaton](https://github.com/ekhyun615-dev/homepage-creaton),
> [homepage-fakohaus](https://github.com/ekhyun615-dev/homepage-fakohaus)는 Astro 정적 사이트입니다.
> caparol.kr만 워드프레스인 이유는 위 표의 "이유" 열 참고.

## 🚦 진행 순서

1. [ ] `docs/ia.md` 검토 — 제품 분류가 실제 취급 품목과 맞는지 확정
2. [ ] `docs/content-plan.md` 의 자료 수집
3. [ ] LocalWP에 워드프레스 설치 → `docs/getting-started.md` 따라하기
4. [ ] `mu-plugins/` 복사 → 고유주소 저장 → 제품/시공사례 메뉴 확인
5. [ ] ACF 필드 생성 → `docs/acf-fields.md` 대로 (자동으로 `acf-json/` 에 저장됨)
6. [ ] 자식 테마 디자인 작업 → `theme/caparol-child/` 에 커밋
7. [ ] 실서버 이전 · 도메인 연결 · SSL
8. [ ] 네이버 서치어드바이저 · 구글 서치콘솔 등록

## 만들어진 화면 (2026-08-26)

디자인은 모두 코드입니다. Elementor 는 헤더에만 씁니다.
파일을 올리면 주소가 자동으로 생깁니다 — 워드프레스에서 페이지를 만들 필요가 없습니다.
(`/contact`, `/about` 두 곳만 슬러그가 같은 빈 페이지가 필요합니다)

| 주소 | 템플릿 파일 |
|---|---|
| `/` | `front-page.php` |
| `/products/` | `archive-product.php` |
| `/products/category/{slug}/` | `taxonomy-product_cat.php` |
| `/products/{제품}/` | `single-product.php` |
| `/references/` | `archive-reference.php` |
| `/references/region/{slug}/` | `taxonomy-reference_region.php` |
| `/references/type/{slug}/` | `taxonomy-reference_type.php` |
| `/references/{현장}/` | `single-reference.php` |
| `/downloads/` | `archive-document.php` |
| `/downloads/type/{slug}/` | `taxonomy-document_type.php` |
| `/downloads/{자료}/` | `single-document.php` |
| `/contact/` | `page-contact.php` ← 슬러그 `contact` 페이지 필요 |
| `/about/` | `page-about.php` ← 슬러그 `about` 페이지 필요 |
| 푸터 (전 화면) | `inc/footer.php` |

### 글을 고칠 때 여는 파일

| 무엇 | 파일 |
|---|---|
| 연락처 · 사업자정보 · 홈 문구 · 신뢰지표 | `inc/site-info.php` |
| 브랜드 소개 문구 · 연혁 | `inc/about-content.php` |
| 그 외 모든 글 | 워드프레스 관리자 |

### 아직 없는 화면

- 컬러 (`/colors/`) — 색상 데이터(색상번호 + HEX) 확보 후
- 공지사항 · 게시판
