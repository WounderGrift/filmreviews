<?php

namespace App\Http\Helpers;

use App\Http\Lib\BDecoder;
use Illuminate\Support\Facades\Storage;

class TorrentHelper
{
    public static function getSeedPeer($path)
    {
        $path    = trim(Storage::url($path), '/');
        $decoder = new BDecoder($path);

        $infoHash = $decoder->getInfoHash($decoder->result['info']);
        die(var_dump($infoHash));
        $announceUrls = $decoder->result['announce-list'];

        foreach ($announceUrls as $announceList) {
            foreach ($announceList as $tracker) {
                try {
                    $parsedUrl = parse_url($tracker);

                    $host = $parsedUrl['host'];
                    $port = $parsedUrl['port'];

                    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
                    if (!$socket) {
                        die('Не удалось создать сокет: ' . socket_strerror(socket_last_error()));
                    }

                    $sent = socket_sendto($socket, $request, strlen($request), 0, $host, $port);
                    if (!$sent) {
                        die('Не удалось отправить запрос: ' . socket_strerror(socket_last_error()));
                    }

                    if (!$response) {
                        die('Не удалось получить ответ: ' . socket_strerror(socket_last_error()));
                    }

                    $response = '';
                    socket_recvfrom($socket, $response, 2048, 0, $host, $port);

                } catch (\Exception $e) {
                    echo 'Ошибка при запросе к трекеру ' . $tracker . ': ' . $e->getMessage() . PHP_EOL;
                }
            }
        }

        die(var_dump(  $decoder->result['announce'], $decoder->result['announce-list']   ));



//        $decoder = new Decoder();
//        $path    = trim(Storage::url($path), '/');
//
//        $decodedFile  = $decoder->decodeFile($path);
//        $announceUrls = $decodedFile['announce-list'] ?? [$decodedFile['announce']];
//        $totalSeeds   = $totalPeers = 0;
//
//        foreach ($announceUrls as $announceList) {
//            foreach ($announceList as $tracker) {
//                try {
//                    $url = $tracker . '?info_hash=' . urlencode($decodedFile['info_hash']);
//
//                    $response = file_get_contents($url);
//                    $trackerData = json_decode($response, true);
//
//                    $totalSeeds += $trackerData['seeds'];
//                    $totalPeers += $trackerData['peers'];
//                } catch (\Exception $e) {
//                    echo 'Ошибка при запросе к трекеру ' . $tracker . ': ' . $e->getMessage() . PHP_EOL;
//                }
//            }
//        }
//
//        return [
//            'seeds' => $totalSeeds,
//            'peers' => $totalPeers
//        ];
    }
}


