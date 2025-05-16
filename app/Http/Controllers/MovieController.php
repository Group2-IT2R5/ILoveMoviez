<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MovieController extends Controller
{
    public function search(Request $request)
    {
        $title = $request->query('title');

        if (!$title) {
            return response()->json(['error' => 'Title is required'], 400);
        }

        // Load API keys
        $tmdbApiKey = env('TMDB_API_KEY');
        $ytApiKey = env('YOUTUBE_API_KEY');
        $giphyApiKey = env('GIPHY_API_KEY'); // Add Giphy API key
        $spotifyClientId = env('SPOTIFY_CLIENT_ID'); // Add Spotify Client ID
        $spotifyClientSecret = env('SPOTIFY_CLIENT_SECRET'); // Add Spotify Client Secret

        if (!$tmdbApiKey || !$ytApiKey || !$giphyApiKey || !$spotifyClientId || !$spotifyClientSecret) {
            return response()->json(['error' => 'API keys are not set properly'], 500);
        }

        $client = new Client();

        try {
            // Step 1: Search movie on TMDb
            $tmdbResponse = $client->request('GET', 'https://api.themoviedb.org/3/search/movie', [
                'query' => [
                    'api_key' => $tmdbApiKey,
                    'query' => $title,
                ],
            ]);

            $tmdbData = json_decode($tmdbResponse->getBody(), true);
            $movie = $tmdbData['results'][0] ?? null;

            if (!$movie) {
                return response()->json(['error' => 'Movie not found'], 404);
            }

            $movieId = $movie['id'];

            // Step 2: Fetch cast from TMDb
            $creditsResponse = $client->request('GET', "https://api.themoviedb.org/3/movie/{$movieId}/credits", [
                'query' => [
                    'api_key' => $tmdbApiKey,
                ],
            ]);

            $creditsData = json_decode($creditsResponse->getBody(), true);

            $cast = array_map(function ($actor) {
                return [
                    'name' => $actor['name'],
                    'character' => $actor['character'],
                ];
            }, array_slice($creditsData['cast'], 0, 5));

            // Step 3: Search for trailer on YouTube
            $ytResponse = $client->request('GET', 'https://www.googleapis.com/youtube/v3/search', [
                'query' => [
                    'key' => $ytApiKey,
                    'q' => $title . ' trailer',
                    'part' => 'snippet',
                    'maxResults' => 1,
                    'type' => 'video',
                ],
            ]);

            $ytData = json_decode($ytResponse->getBody(), true);
            $ytVideo = $ytData['items'][0] ?? null;

            // Step 4: Search for GIFs on Giphy
            $giphyResponse = $client->request('GET', 'https://api.giphy.com/v1/gifs/search', [
                'query' => [
                    'api_key' => $giphyApiKey,
                    'q' => $title,
                    'limit' => 1, // Limit to 1 GIF
                ],
            ]);

            $giphyData = json_decode($giphyResponse->getBody(), true);
            $giphyGif = $giphyData['data'][0]['images']['original']['url'] ?? null;

            // Step 5: Get access token for Spotify API
            $spotifyTokenResponse = $client->request('POST', 'https://accounts.spotify.com/api/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $spotifyClientId,
                    'client_secret' => $spotifyClientSecret,
                ],
            ]);
            
            $spotifyTokenData = json_decode($spotifyTokenResponse->getBody(), true);
            $spotifyAccessToken = $spotifyTokenData['access_token'] ?? null;

            if (!$spotifyAccessToken) {
                return response()->json(['error' => 'Unable to retrieve Spotify access token'], 500);
            }

            // Step 6: Search for a Spotify playlist based on the movie title
            $spotifyResponse = $client->request('GET', 'https://api.spotify.com/v1/search', [
                'query' => [
                    'q' => $title,
                    'type' => 'playlist',
                    'limit' => 1,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $spotifyAccessToken,
                ],
            ]);

            $spotifyData = json_decode($spotifyResponse->getBody(), true);
            $spotifyPlaylistUrl = $spotifyData['playlists']['items'][0]['external_urls']['spotify'] ?? null;

            // Step 7: Build the response
            $result = [
                'movie' => [
                    'title' => $movie['title'] ?? 'N/A',
                    'overview' => $movie['overview'] ?? 'N/A',
                    'release_date' => $movie['release_date'] ?? 'N/A',
                    'rating' => $movie['vote_average'] ?? 'N/A',
                    'language' => $movie['original_language'] ?? 'N/A',
                    'poster' => $movie['poster_path']
                        ? 'https://image.tmdb.org/t/p/w500' . $movie['poster_path']
                        : 'N/A',
                    'cast' => $cast,
                ],
                'trailer' => [
                    'title' => $ytVideo['snippet']['title'] ?? 'No trailer found',
                    'video_url' => isset($ytVideo['id']['videoId'])
                        ? 'https://www.youtube.com/watch?v=' . $ytVideo['id']['videoId']
                        : null,
                ],
                'giphy' => [
                    'gif_url' => $giphyGif ?? 'No GIF found',
                ],
                'spotify' => [
                    'playlist_url' => $spotifyPlaylistUrl ?? 'No playlist found',
                ]
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => 'API request failed', 'details' => $e->getMessage()], 500);
        }
    }
}
