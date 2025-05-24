<?php

namespace App\Services;

use GuzzleHttp\Client;

class YouTubeService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('YOUTUBE_API_KEY');

        $this->client = new Client([
            'base_uri' => env('YOUTUBE_BASE_URI'),
            'timeout'  => env('YOUTUBE_TIMEOUT'),
        ]);
    }

    public function getTrailer($title)
    {
        $response = $this->client->get('search', [
            'query' => [
                'key' => $this->apiKey,
                'q' => $title . ' trailer',
                'part' => 'snippet',
                'maxResults' => 1,
                'type' => 'video',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $video = $data['items'][0] ?? null;

        return [
            'title' => $video['snippet']['title'] ?? 'No trailer found',
            'video_url' => isset($video['id']['videoId'])
                ? 'https://www.youtube.com/watch?v=' . $video['id']['videoId']
                : null,
        ];
    }
}
