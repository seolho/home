<?php
declare(strict_types=1);

function col_to_index(string $letters): int {
    $n=0; foreach(str_split($letters) as $c){ $n=$n*26+(ord(strtoupper($c))-64); } return $n-1;
}
function read_xlsx_rows(string $file): array {
    if (!class_exists('ZipArchive')) throw new RuntimeException('서버에 PHP ZipArchive 확장이 필요합니다.');
    $z=new ZipArchive(); if($z->open($file)!==true) throw new RuntimeException('엑셀 파일을 열 수 없습니다.');
    $shared=[];
    $s=$z->getFromName('xl/sharedStrings.xml');
    if($s!==false){
        $xml=simplexml_load_string($s);
        if($xml){ foreach($xml->si as $si){ $texts=[]; if(isset($si->t)) $texts[]=(string)$si->t; foreach($si->r as $r) $texts[]=(string)$r->t; $shared[]=implode('',$texts); } }
    }
    $sheet=$z->getFromName('xl/worksheets/sheet1.xml');
    if($sheet===false) throw new RuntimeException('첫 번째 시트를 찾을 수 없습니다.');
    $xml=simplexml_load_string($sheet); $rows=[];
    foreach($xml->sheetData->row as $r){
        $out=[];
        foreach($r->c as $c){
            $ref=(string)$c['r']; preg_match('/([A-Z]+)\d+/',$ref,$m); $idx=col_to_index($m[1]??'A');
            $type=(string)$c['t']; $v=(string)$c->v;
            if($type==='s') $v=$shared[(int)$v]??'';
            elseif($type==='inlineStr') $v=(string)$c->is->t;
            $out[$idx]=$v;
        }
        if($out){ ksort($out); $max=max(array_keys($out)); $dense=[]; for($i=0;$i<=$max;$i++) $dense[]=(string)($out[$i]??''); $rows[]=$dense; }
    }
    $z->close(); return $rows;
}
function read_csv_rows(string $file): array {
    $rows=[]; $fh=fopen($file,'rb'); if(!$fh) return [];
    while(($r=fgetcsv($fh))!==false){
        foreach($r as &$v){ if(!mb_check_encoding($v,'UTF-8')) $v=mb_convert_encoding($v,'UTF-8','CP949,EUC-KR,UTF-8'); }
        unset($v); $rows[]=$r;
    }
    fclose($fh); return $rows;
}
