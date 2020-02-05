<?php
declare(strict_types=1);

if(!isset($argv[1])){
    echo "Must set getData.json file.\n";
    exit(1);
}

$kcAPIgetDataJsonFile = $argv[1];
$shipJsonFile = __DIR__ . '/jsons/kcships.json';
$portBgmJsonFile = __DIR__ . '/jsons/kcportbgms.json';
$battleBgmJsonFile = __DIR__ . '/jsons/kcbattlebgms.json';
mkdir(__DIR__ . '/jsons');

$getDataFile = file_get_contents($kcAPIgetDataJsonFile);
$getDataFile = removeUtf8Bom($getDataFile);
$dataJsonStr = preg_replace('/^svdata=/', '', $getDataFile, 1);
$json = json_decode($dataJsonStr, true);

$ships = getShipData($json);
file_put_contents($shipJsonFile, json_encode($ships, JSON_UNESCAPED_UNICODE));

$portBgms = getPortBgmData($json);
file_put_contents($portBgmJsonFile, json_encode($portBgms, JSON_UNESCAPED_UNICODE));

$battleBgms = getBattleBgmData($json);
file_put_contents($battleBgmJsonFile, json_encode($battleBgms, JSON_UNESCAPED_UNICODE));

function getShipData(array $json): array{
    $result = [];
    foreach($json['api_data']['api_mst_ship'] as $ship){
        $intro = $ship['api_getmes'] ?? '';
        if($intro == '<br>'){
            $intro = '';
        }else{
            $intro = str_replace('<br>', "\n", $intro);
        }

        foreach($json['api_data']['api_mst_shipgraph'] as $shipgraph){
            if($ship['api_id'] == $shipgraph['api_id']){
                $code = $shipgraph['api_filename'];
            }
        }

        $result[] = [
            'id' => $ship['api_id'],
            'name' => $ship['api_name'],
            'yomi' => $ship['api_yomi'],
            'code' => $code,
            'intro' => $intro
        ];
    }
    return $result;
}

function getPortBgmData(array $json): array{
    $result = [];
    foreach($json['api_data']['api_mst_bgm'] as $bgm){
        $result[] = [
            'id' => $bgm['api_id'],
            'name' => $bgm['api_name']
        ];
    }
    return $result;
}

function getBattleBgmData(array $json): array{
    $result = [];
    foreach($json['api_data']['api_mst_mapbgm'] as $mapbgm){
        $mapNumber = $mapbgm['api_maparea_id'] . '-' . $mapbgm['api_no'];
        $result[$mapNumber] = [
            'movingBgm' => $mapbgm['api_moving_bgm'],
            'mapBgm' => [
                'noon' => $mapbgm['api_map_bgm'][0],
                'night' => $mapbgm['api_map_bgm'][1]
            ],
            'bossBgm' => [
                'noon' => $mapbgm['api_boss_bgm'][0],
                'night' => $mapbgm['api_boss_bgm'][1]
            ]
        ];
    }
    return $result;
}

function removeUtf8Bom(string $text): string{
    $bom = pack('H*','EFBBBF');
    return preg_replace("/^$bom/", '', $text);
}
