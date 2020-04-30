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
        $ip = $request->header('x-real-ip');

        $response = Http::get('http://api.map.baidu.com/location/ip', [
            'ak' => 'GC7Sc8TSzX89mqKOZ3QReqDNxbnKz8Ys',
            'ip' => $ip,
            'coor' => 'bd09ll'
        ]);

        if (!$response->successful()) {
            return view('message', ['message' => 'Error1']);
        }

        $data = $response->json();

        if ($data['status']) {
            return view('message', ['message' => 'Error2']);
        }

        $geoHash = GeoHash::encode($data['content']['point']['y'], $data['content']['point']['x']);
        $locationName = $data['content']['address'];

        $exists = WorldLog::where('ip', $ip)->exists();
        if ($exists) {
            return view('message', ['message' => 'Success']);
        }

        $worldLog = WorldLog::create(['ip' => $ip, 'location_name' => $locationName,]);
        if ($worldLog) {
            $world = World::where('geo_hash', $geoHash)->first();

            if ($world) {
                World::where('geo_hash', $geoHash)->increment('metric');
            } else {
                World::create(['location_name' => $locationName, 'geo_hash' => $geoHash, 'metric' => 1]);
            }

            return view('message', ['message' => 'Success']);
        }

        return view('message', ['message' => 'Error3']);
    }
}
