# mu-plugins/

사이트의 **콘텐츠 구조**를 정의하는 코드입니다.

```
mu-plugins/
├─ caparol-core-loader.php     ← mu-plugins는 하위 폴더를 자동 로드하지 않아 로더가 필요
└─ caparol-core/
   ├─ caparol-core.php
   └─ inc/
      ├─ post-types.php        제품 · 시공사례 · 색상 · 기술자료
      ├─ taxonomies.php        제품 카테고리 · 건물용도 · 지역 · 색상계열 · 자료구분
      └─ security.php          버전 노출 제거, XML-RPC 차단, 파일명 정리 등
```

## 왜 플러그인(CPT UI)이 아니라 코드인가

| | CPT UI 플러그인 | mu-plugin 코드 |
|---|---|---|
| 실수로 비활성화 | 가능 → **제품 전체가 사라져 보임** | 불가능 (must-use) |
| 버전 관리 | ✗ DB에만 존재 | ✓ Git에 남음 |
| 로컬 → 서버 이전 | 수동 재설정 | 파일 복사로 끝 |
| 플러그인 개수 | +1 | +0 |

## 설치

`mu-plugins/` 폴더 안의 내용을 서버의 `wp-content/mu-plugins/` 에 그대로 복사합니다.
(`mu-plugins` 폴더가 없으면 직접 만드세요.)

복사 후 **[설정 → 고유주소]에 들어가 저장** 한 번 하세요. 안 하면 제품 페이지가 404가 납니다.
