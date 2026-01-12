<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class VoiceController extends Controller
{
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:wav,mp3,ogg|max:10240',
        ]);

        $client = new Client([
            'timeout' => 60,
        ]);

        $response = $client->post('http://localhost:8001/transcribe', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($request->file('audio')->getPathname(), 'r'),
                    'filename' => $request->file('audio')->getClientOriginalName(),
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return response()->json([
            'text' => $data['text'],
        ]);
    }
}
