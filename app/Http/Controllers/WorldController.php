<?php

namespace App\Http\Controllers;

use App\World;
use App\WorldLog;
use GeoHash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WorldController extends Controller
{
    public function index(Request $request)
    {
        $ip = $request->ip();

        $response = Http::get('http://api.map.baidu.com/location/ip', [
            'ak' => 'GC7Sc8TSzX89mqKOZ3QReqDNxbnKz8Ys',
            'ip' => $ip,
            'coor' => 'bd09ll'
        ]);

        if (!$response->successful()) {
            return view('message', ['message' => '垃圾百度地图，请求接口失败！']);
        }

        $data = $response->json();

        if ($data['status']) {
            return view('message', ['message' => '垃圾百度地图，错误原因：'.$data['message']]);
        }

        $geoHash = GeoHash::encode($data['content']['point']['y'], $data['content']['point']['x']);
        $locationName = $data['content']['address'];

        $exists = WorldLog::where('ip', $ip)->exists();
        if ($exists) {
            return view('message', ['message' => '已存在！勿重复请求，小水管！']);
        }

        $worldLog = WorldLog::create(['ip' => $ip, 'location_name' => $locationName, 'header' => $request->header()]);
        if ($worldLog) {
            $world = World::where('geo_hash', $geoHash)->first();

            if ($world) {
                World::where('geo_hash', $geoHash)->increment('metric');
            } else {
                World::create(['location_name' => $locationName, 'geo_hash' => $geoHash, 'metric' => 1]);
            }

            return view('message', ['message' => '点击查看地图']);
        }

        return view('message', ['message' => '你干了什么？？？']);
    }
}
