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
| **자식 테마 코드** | `wp-content/themes/caparol-child/` | ✓ `theme/` |
| **설계서·세팅·운영 문서** | — | ✓ `docs/` |
| **로고·브랜드 원본** | — | ✓ `brand/` |
| `wp-config.php` (DB 비밀번호) | 서버 | ✗ **절대 금지** |

> 이 구분이 흐려지면 사고가 납니다. 사이트 복구는 Git이 아니라 **백업 플러그인**이 담당합니다.

## 🗂 문서

| 문서 | 내용 | 언제 보나 |
|---|---|---|
| [docs/ia.md](docs/ia.md) | 사이트 구조 설계서 — 메뉴·페이지·제품 분류·페이지별 구성 | **워드프레스 만지기 전에 먼저** |
| [docs/wordpress-setup.md](docs/wordpress-setup.md) | 테마·플러그인·보안·SEO 초기 세팅 순서 | 구축 시작할 때 |
| [docs/content-plan.md](docs/content-plan.md) | 확보해야 할 자료 목록 (사진·PDF·회사정보) | 자료 수집할 때 |
| [docs/operations.md](docs/operations.md) | 백업 주기·업데이트 규칙·장애 대응 | 오픈 후 상시 |

## 🛠 기술 스택

| 항목 | 결정 | 이유 |
|---|---|---|
| 플랫폼 | **WordPress** | 직원이 직접·자주 콘텐츠를 수정하고, 게시판/자료실이 필요함 |
| 호스팅 | PHP 8.x + MySQL 지원 요금제 (카페24 등) | 정적 요금제로는 불가 |
| 테마 | 가벼운 베이스 테마 + **자식 테마** | 올인원 유료 테마는 무겁고 나중에 못 바꿈 |
| 개발 환경 | [LocalWP](https://localwp.com) (로컬 워드프레스) | 실서버 건드리지 않고 실험 |

> 자매 사이트 [homepage-creaton](https://github.com/ekhyun615-dev/homepage-creaton),
> [homepage-fakohaus](https://github.com/ekhyun615-dev/homepage-fakohaus)는 Astro 정적 사이트입니다.
> caparol.kr만 워드프레스인 이유는 위 표의 "이유" 열 참고.

## 🚦 진행 순서

1. [ ] `docs/ia.md` 검토 — 제품 분류가 실제 취급 품목과 맞는지 확정
2. [ ] `docs/content-plan.md` 의 자료 수집
3. [ ] LocalWP에 워드프레스 설치 → `docs/wordpress-setup.md` 순서대로 세팅
4. [ ] 자식 테마 작업 → `theme/caparol-child/` 에 커밋
5. [ ] 실서버 이전 · 도메인 연결 · SSL
6. [ ] 네이버 서치어드바이저 · 구글 서치콘솔 등록
