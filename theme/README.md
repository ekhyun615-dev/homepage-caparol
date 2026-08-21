# theme/

자식 테마(child theme) 코드가 들어가는 곳입니다.

```
theme/
└─ caparol-child/        ← 서버의 wp-content/themes/caparol-child/ 와 동일
   ├─ style.css
   ├─ functions.php
   └─ templates/
```

## 동기화 방법

로컬(LocalWP)의 `wp-content/themes/caparol-child/` 폴더를 이 폴더와 동기화합니다.
심볼릭 링크를 걸어두면 편합니다.

```bash
# macOS / Linux
ln -s ~/Local\ Sites/caparol/app/public/wp-content/themes/caparol-child theme/caparol-child
```

## 넣지 말아야 할 것

- 부모 테마 (GeneratePress 등) — 업데이트로 관리
- 플러그인
- 업로드된 이미지
