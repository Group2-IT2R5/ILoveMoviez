<?php

namespace App\Services;

use GuzzleHttp\Client;

class GiphyService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GIPHY_API_KEY');

        $this->client = new Client([
            'base_uri' => env('GIPHY_BASE_URI'),
            'timeout'  => env('GIPHY_TIMEOUT'),
        ]);
    }

    public function getGif($title)
    {
        $response = $this->client->get('gifs/search', [
            'query' => [
                'api_key' => $this->apiKey,
                'q'       => $title,
                'limit'   => 1,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $gif = $data['data'][0]['images']['original']['url'] ?? null;

        return ['gif_url' => $gif ?? 'No GIF found'];
    }
}
