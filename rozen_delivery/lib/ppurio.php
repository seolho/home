<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';

/**
 * 로젠택배 배송안내 알림톡
 * Bizppurio 카카오 알림톡 전용 API (/v1/kakao)
 *
 * 검증된 group_shipping 발송 구조와 동일하게 구성
 * - messageType: ALT
 * - refKey: 요청 최상위
 * - isResend: N
 * - targetCount 필수
 * - targets: to / name / changeWord
 * - 배송조회 버튼은 승인 템플릿의 DS 버튼을 사용하므로 payload에 버튼/URL 미전달
 *
 * 템플릿 치환
 * [*이름*] = name
 * [*1*]     = 상품명(var1)
 * [*2*]     = 송장번호(var2)
 * 택배사명  = 로젠택배(템플릿 고정)
 */

function ppurio_require_config(): void
{
    foreach ([
        'PPURIO_ACCOUNT',
        'PPURIO_AUTH_KEY',
        'PPURIO_SENDER_PROFILE',
        'PPURIO_TEMPLATE_CODE',
    ] as $key) {
        if (!defined($key) || trim((string)constant($key)) === '') {
            throw new RuntimeException($key . ' 설정값이 config.php에 없습니다.');
        }
    }
}

/**
 * Bizppurio HTTP POST 공통 처리
 */
function ppurio_http(string $url, array $headers, ?string $body = null): array
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body ?? '{}',
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        throw new RuntimeException('뿌리오 통신 오류: ' . $error);
    }

    $json = json_decode((string)$raw, true);

    if (!is_array($json)) {
        throw new RuntimeException(
            '뿌리오 응답 JSON 오류 (HTTP ' . $http . '): ' .
            mb_substr((string)$raw, 0, 500, 'UTF-8')
        );
    }

    return [
        'http' => $http,
        'json' => $json,
        'raw'  => (string)$raw,
    ];
}

/**
 * 토큰 발급
 */
function ppurio_token(): string
{
    ppurio_require_config();

    $basic = base64_encode(
        (string)PPURIO_ACCOUNT . ':' . (string)PPURIO_AUTH_KEY
    );

    $base = defined('PPURIO_API_BASE')
        ? rtrim((string)PPURIO_API_BASE, '/')
        : 'https://message.ppurio.com';

    $r = ppurio_http(
        $base . '/v1/token',
        [
            'Authorization: Basic ' . $basic,
            'Content-Type: application/json; charset=utf-8',
        ],
        '{}'
    );

    $token = (string)($r['json']['token'] ?? '');

    if ($token === '') {
        $msg = (string)(
            $r['json']['description']
            ?? $r['json']['message']
            ?? '토큰 발급 실패'
        );
        throw new RuntimeException($msg);
    }

    return $token;
}

/**
 * API 요청 단위 refKey
 * 영문+숫자만 사용하고 짧게 유지
 */
function ppurio_request_ref(): string
{
    return 'LZN' . date('ymdHis') . strtoupper(bin2hex(random_bytes(4)));
}

function ppurio_normalize_phone(string $phone): string
{
    return preg_replace('/\D+/', '', $phone) ?? '';
}

function ppurio_normalize_tracking(string $tracking): string
{
    // 엑셀에서 숫자로 들어와 공백/하이픈이 섞여도 숫자만 발송
    return preg_replace('/\D+/', '', trim($tracking)) ?? '';
}

/**
 * DB 1행을 실제 발송값으로 검증/정규화
 */
function ppurio_validate_delivery(array $row): array
{
    $name = trim((string)($row['name'] ?? ''));
    $phone = ppurio_normalize_phone((string)($row['phone'] ?? ''));
    $product = trim((string)($row['product_name'] ?? ''));
    $tracking = ppurio_normalize_tracking((string)($row['tracking_no'] ?? ''));

    if ($name === '') {
        throw new RuntimeException('수신자 성명이 없습니다.');
    }

    if (strlen($phone) < 10 || strlen($phone) > 11) {
        throw new RuntimeException($name . '님의 연락처가 올바르지 않습니다.');
    }

    if ($product === '') {
        throw new RuntimeException($name . '님의 상품명이 없습니다.');
    }

    if ($tracking === '') {
        throw new RuntimeException($name . '님의 송장번호가 없습니다.');
    }

    return [
        'name' => $name,
        'phone' => $phone,
        'product_name' => $product,
        'tracking_no' => $tracking,
    ];
}

/**
 * Bizppurio 접수결과 공통 판정
 */
function ppurio_parse_result(array $r, string $refKey): array
{
    $j = $r['json'];

    $code = (string)(
        $j['code']
        ?? $j['resultCode']
        ?? ''
    );

    $description = (string)(
        $j['description']
        ?? $j['message']
        ?? ''
    );

    // 최근 정상 운영 코드와 동일: HTTP 2xx를 우선 성공으로 보고,
    // 명시적인 오류 코드가 있으면 실패 처리
    $ok = ($r['http'] >= 200 && $r['http'] < 300);

    if ($code !== '' && !in_array($code, [
        '1000', '200', '0', 'OK', 'SUCCESS'
    ], true)) {
        $ok = false;
    }

    return [
        'ok' => $ok,
        'http' => $r['http'],
        'code' => $code,
        'message' => $ok
            ? ($description !== '' ? $description : '발송요청 성공')
            : ($description !== '' ? $description : '발송 실패'),
        'description' => $description,
        'message_key' => (string)(
            $j['messageKey']
            ?? $j['requestKey']
            ?? ''
        ),
        'request_key' => (string)(
            $j['messageKey']
            ?? $j['requestKey']
            ?? $refKey
        ),
        'ref_key' => $refKey,
        'raw' => $r['raw'],
        'response' => $j,
    ];
}

/**
 * 개별 배송안내 알림톡 발송
 * 기존 admin/send_one.php / admin/send_bulk.php와 호환되는 함수명 유지
 */
function ppurio_send_delivery(array $row): array
{
    ppurio_require_config();

    $v = ppurio_validate_delivery($row);
    $token = ppurio_token();
    $refKey = ppurio_request_ref();

    $payload = [
        'account' => (string)PPURIO_ACCOUNT,
        'messageType' => 'ALT',
        'senderProfile' => (string)PPURIO_SENDER_PROFILE,
        'templateCode' => (string)PPURIO_TEMPLATE_CODE,
        'duplicateFlag' => 'N',

        // 검증된 정상 운영 코드와 동일하게 최상위에 위치
        'refKey' => $refKey,

        // 알림톡 실패 시 SMS/LMS 대체발송 사용 안 함
        'isResend' => 'N',

        'targetCount' => 1,
        'targets' => [[
            'to' => $v['phone'],

            // 템플릿 [*이름*]
            'name' => $v['name'],

            // 템플릿 [*1*] = 상품명, [*2*] = 송장번호
            'changeWord' => [
                'var1' => $v['product_name'],
                'var2' => $v['tracking_no'],
            ],
        ]],
    ];

    $body = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($body === false) {
        throw new RuntimeException('알림톡 JSON 생성 실패');
    }

    $base = defined('PPURIO_API_BASE')
        ? rtrim((string)PPURIO_API_BASE, '/')
        : 'https://message.ppurio.com';

    $r = ppurio_http(
        $base . '/v1/kakao',
        [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json; charset=utf-8',
        ],
        $body
    );

    $ret = ppurio_parse_result($r, $refKey);

    // 현재 시스템 로그 테이블에서 request/response를 그대로 저장하므로 유지
    $ret['request'] = $payload;
    $ret['response'] = $r['json'];

    return $ret;
}
