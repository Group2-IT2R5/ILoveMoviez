<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SpotifyService
{
    protected $client;
    protected $clientId;
    protected $clientSecret;
    protected $tokenUrl;
    protected $searchUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->clientId = env('SPOTIFY_CLIENT_ID');
        $this->clientSecret = env('SPOTIFY_CLIENT_SECRET');
        $this->tokenUrl = env('SPOTIFY_TOKEN_URL', 'https://accounts.spotify.com/api/token');
        $this->searchUrl = env('SPOTIFY_SEARCH_URL', 'https://api.spotify.com/v1/search');
    }

    protected function getAccessToken(): ?string
    {
        try {
            $response = $this->client->post($this->tokenUrl, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['access_token'] ?? null;

        } catch (RequestException $e) {
            return null;
        }
    }

    public function getPlaylist(string $title): array
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return ['error' => 'Unable to authenticate with Spotify'];
        }

        try {
            $response = $this->client->get($this->searchUrl, [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'query' => [
                    'q' => $title,
                    'type' => 'playlist',
                    'limit' => 1,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $playlistUrl = $data['playlists']['items'][0]['external_urls']['spotify'] ?? null;

            return $playlistUrl
                ? ['playlist_url' => $playlistUrl]
                : ['error' => 'No playlist found'];

        } catch (RequestException $e) {
            return ['error' => 'Spotify request failed'];
        }
    }
}
