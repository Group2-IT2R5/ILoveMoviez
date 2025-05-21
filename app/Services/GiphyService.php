<?php

namespace App\Services;

use GuzzleHttp\Client;

class GiphyService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('GIPHY_API_KEY');
    }

    public function getGif($title)
    {
        $response = $this->client->get('https://api.giphy.com/v1/gifs/search', [
            'query' => [
                'api_key' => $this->apiKey,
                'q' => $title,
                'limit' => 1,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $gif = $data['data'][0]['images']['original']['url'] ?? null;

        return ['gif_url' => $gif ?? 'No GIF found'];
    }
}
