<?php

namespace App\Services;

use GuzzleHttp\Client;

class SpotifyService
{
    protected $client;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->client = new Client();
        $this->clientId = env('SPOTIFY_CLIENT_ID');
        $this->clientSecret = env('SPOTIFY_CLIENT_SECRET');
    }

    public function getAccessToken()
    {
        $response = $this->client->post('https://accounts.spotify.com/api/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'] ?? null;
    }

    public function getPlaylist($title)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['playlist_url' => 'No access token'];
        }

        $response = $this->client->get('https://api.spotify.com/v1/search', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'query' => [
                'q' => $title,
                'type' => 'playlist',
                'limit' => 1,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $playlist = $data['playlists']['items'][0]['external_urls']['spotify'] ?? null;

        return ['playlist_url' => $playlist ?? 'No playlist found'];
    }
}
