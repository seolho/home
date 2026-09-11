<?php
declare(strict_types=1);
require_once __DIR__ . '/common.php';

/**
 * 다중 택배사 배송안내 알림톡
 * 템플릿 치환:
 * [*이름*] = name
 * [*1*] = 상품명(var1)
 * [*2*] = 택배사명(var2)
 * [*3*] = 송장번호(var3)
 * 배송조회(DS) 버튼은 승인 템플릿 자체 버튼 사용.
 */
function ppurio_require_config(): void {
    foreach (['PPURIO_ACCOUNT','PPURIO_AUTH_KEY','PPURIO_SENDER_PROFILE','PPURIO_TEMPLATE_CODE'] as $key) {
        if (!defined($key) || trim((string)constant($key)) === '') throw new RuntimeException($key.' 설정값이 config.php에 없습니다.');
    }
}
function ppurio_http(string $url,array $headers,?string $body=null): array {
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>$headers,CURLOPT_POSTFIELDS=>$body??'{}']);
    $raw=curl_exec($ch);$errno=curl_errno($ch);$error=curl_error($ch);$http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    if($errno!==0) throw new RuntimeException('뿌리오 통신 오류: '.$error);
    $json=json_decode((string)$raw,true);
    if(!is_array($json)) throw new RuntimeException('뿌리오 응답 JSON 오류 (HTTP '.$http.'): '.mb_substr((string)$raw,0,500,'UTF-8'));
    return ['http'=>$http,'json'=>$json,'raw'=>(string)$raw];
}
function ppurio_token(): string {
    ppurio_require_config();
    $basic=base64_encode((string)PPURIO_ACCOUNT.':'.(string)PPURIO_AUTH_KEY);
    $base=defined('PPURIO_API_BASE')?rtrim((string)PPURIO_API_BASE,'/'):'https://message.ppurio.com';
    $r=ppurio_http($base.'/v1/token',['Authorization: Basic '.$basic,'Content-Type: application/json; charset=utf-8'],'{}');
    $token=(string)($r['json']['token']??'');
    if($token==='') throw new RuntimeException((string)($r['json']['description']??$r['json']['message']??'토큰 발급 실패'));
    return $token;
}
function ppurio_request_ref(): string { return 'DLM'.date('ymdHis').strtoupper(bin2hex(random_bytes(4))); }
function ppurio_parse_result(array $r,string $refKey): array {
    $j=$r['json'];$code=(string)($j['code']??$j['resultCode']??'');$description=(string)($j['description']??$j['message']??'');
    $ok=($r['http']>=200&&$r['http']<300);
    if($code!==''&&!in_array($code,['1000','200','0','OK','SUCCESS'],true))$ok=false;
    return ['ok'=>$ok,'http'=>$r['http'],'code'=>$code,'message'=>$ok?($description!==''?$description:'발송요청 성공'):($description!==''?$description:'발송 실패'),'description'=>$description,'message_key'=>(string)($j['messageKey']??$j['requestKey']??''),'request_key'=>(string)($j['messageKey']??$j['requestKey']??$refKey),'ref_key'=>$refKey,'raw'=>$r['raw'],'response'=>$j];
}
function ppurio_validate_delivery(array $row): array {
    $name=trim((string)($row['name']??''));
    $phone=normalize_phone((string)($row['phone']??''));
    $product=trim((string)($row['product_name']??''));
    $carrier=normalize_carrier((string)($row['carrier_name']??''));
    $tracking=normalize_tracking((string)($row['tracking_no']??''));
    if($name==='')throw new RuntimeException('수신자 성명이 없습니다.');
    if(strlen($phone)<10||strlen($phone)>11)throw new RuntimeException($name.'님의 연락처가 올바르지 않습니다.');
    if($product==='')throw new RuntimeException($name.'님의 상품명이 없습니다.');
    if($carrier==='')throw new RuntimeException($name.'님의 택배사명이 지원 목록과 일치하지 않습니다.');
    if($tracking==='')throw new RuntimeException($name.'님의 송장번호가 없습니다.');
    return ['name'=>$name,'phone'=>$phone,'product_name'=>$product,'carrier_name'=>$carrier,'tracking_no'=>$tracking];
}
function ppurio_send_delivery(array $row): array {
    ppurio_require_config();$v=ppurio_validate_delivery($row);$token=ppurio_token();$refKey=ppurio_request_ref();
    $payload=['account'=>(string)PPURIO_ACCOUNT,'messageType'=>'ALT','senderProfile'=>(string)PPURIO_SENDER_PROFILE,'templateCode'=>(string)PPURIO_TEMPLATE_CODE,'duplicateFlag'=>'N','refKey'=>$refKey,'isResend'=>'N','targetCount'=>1,'targets'=>[[]]];
    $payload['targets'][0]=['to'=>$v['phone'],'name'=>$v['name'],'changeWord'=>['var1'=>$v['product_name'],'var2'=>$v['carrier_name'],'var3'=>$v['tracking_no']]];
    $body=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);if($body===false)throw new RuntimeException('알림톡 JSON 생성 실패');
    $base=defined('PPURIO_API_BASE')?rtrim((string)PPURIO_API_BASE,'/'):'https://message.ppurio.com';
    $r=ppurio_http($base.'/v1/kakao',['Authorization: Bearer '.$token,'Content-Type: application/json; charset=utf-8'],$body);
    $ret=ppurio_parse_result($r,$refKey);$ret['request']=$payload;$ret['response']=$r['json'];return $ret;
}
