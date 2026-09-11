[로젠택배 배송안내 알림톡 관리시스템]

1. 폴더를 Cafe24 서버 public_html 아래에 업로드
   예: /lozen_delivery_manager

2. config.php 수정
   - APP_BASE_URL
   - PPURIO_ACCOUNT
   - PPURIO_AUTH_KEY
   - PPURIO_SENDER_PROFILE
   - PPURIO_TEMPLATE_CODE
   - 공통 DB 연결 함수 확인

3. make_hash.php에서 관리자 비밀번호 해시 생성 후
   config.php의 ADMIN_PASSWORD_HASH 교체
   완료 후 make_hash.php 삭제 권장

4. install.php 1회 실행
   테이블 생성 후 install.php 삭제 권장

5. 관리자 접속
   /lozen_delivery_manager/admin/login.php

6. 엑셀 형식
   성명 | 연락처 | 상품명 | 송장번호
   택배사명은 '로젠택배'로 자동 저장됩니다.
   XLSX 첫 번째 시트 또는 CSV 지원.
   송장번호는 엑셀 '텍스트' 형식을 권장합니다.

7. 알림톡 변수 매핑
   [*이름*] = targets[].name
   [*1*]     = changeWord.var1 = 상품명
   택배사명  = 로젠택배 (템플릿 고정값)
   [*2*]     = changeWord.var2 = 송장번호

8. 배송조회 버튼
   템플릿 자체에 '배송조회' 버튼이 승인되어 있으므로 PHP에서 URL/버튼정보를 보내지 않습니다.
   본문에 '택배사명: 로젠택배', '송장번호: [*2*]'가 포함된 승인 템플릿을 사용합니다.

9. 전체 발송
   기본은 미발송(pending)+실패(failed)만 발송합니다.
   이미 성공한 건은 개별 '재발송' 버튼으로 재전송합니다.
   대량 발송 시 서버/비즈뿌리오 정책에 맞춰 배치 크기 조정이 필요할 수 있습니다.

10. 다른 택배사 복제
   폴더 복사 후 config.php의 FIXED_CARRIER_NAME과 PPURIO_TEMPLATE_CODE만 바꾸면 됩니다.
   단, 카카오 배송조회 버튼 지원 택배사명/송장번호 패턴을 해당 택배사 규격에 맞춰야 합니다.

[중요]
- 운영 계정/인증키는 가능하면 /private_config 아래로 이동하세요.
- 현재 ppurio.php는 비즈뿌리오 v1 token + /v1/kakao, messageType=ALT 방식입니다.
- 기존에 사용 중인 귀사 전용 sender 구현이 있다면 lib/ppurio.php만 교체해서 그대로 사용할 수 있습니다.
