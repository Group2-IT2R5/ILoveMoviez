<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SpotifyController extends Controller
{
    public function search(Request $request)
    {
        $movieTitle = $request->query('movie');

        if (!$movieTitle) {
            return response()->json(['error' => 'Movie title is required'], 400);
        }

        // Load Spotify credentials from .env
        $clientId = env('SPOTIFY_CLIENT_ID');
        $clientSecret = env('SPOTIFY_CLIENT_SECRET');

        if (!$clientId || !$clientSecret) {
            return response()->json(['error' => 'Spotify API credentials not set'], 500);
        }

        $accessToken = $this->getSpotifyAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            return response()->json(['error' => 'Failed to authenticate with Spotify'], 500);
        }

        // Search Spotify for soundtrack albums or tracks related to the movie
        $results = $this->searchSpotifySoundtrack($accessToken, $movieTitle);

        return response()->json($results);
    }

    private function getSpotifyAccessToken($clientId, $clientSecret)
    {
        $client = new Client();

        $response = $client->post('https://accounts.spotify.com/api/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode("{$clientId}:{$clientSecret}"),
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['access_token'] ?? null;
    }

    private function searchSpotifySoundtrack($accessToken, $query)
    {
        $client = new Client();

        $response = $client->get("https://api.spotify.com/v1/search", [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'query' => [
                'q' => $query . ' soundtrack',
                'type' => 'album,track',
                'limit' => 3,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        $albums = array_map(function ($album) use ($query) {
            return [
                'movie' => $query,
                'soundtrack_title' => $album['name'] ?? 'N/A',
                'artist' => $album['artists'][0]['name'] ?? 'N/A',
            ];
        }, $data['albums']['items'] ?? []);

        $tracks = array_map(function ($track) use ($query) {
            return [
                'movie' => $query,
                'soundtrack_title' => $track['name'] ?? 'N/A',
                'artist' => $track['artists'][0]['name'] ?? 'N/A',
            ];
        }, $data['tracks']['items'] ?? []);

        return [
            'albums' => $albums,
            'tracks' => $tracks,
        ];
    }
}
