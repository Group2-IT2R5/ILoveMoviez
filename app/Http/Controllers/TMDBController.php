<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TMDbController extends Controller
{
    public function search(Request $request)
    {
        $title = $request->query('title');

        if (!$title) {
            return response()->json(['error' => 'Title is required'], 400);
        }

        // Load TMDb API key from the .env file
        $tmdbApiKey = env('TMDB_API_KEY');

        if (!$tmdbApiKey) {
            return response()->json(['error' => 'TMDb API key is not set'], 500);
        }

        $client = new Client();

        try {
            // Step 1: Search for movie on TMDb
            $tmdbResponse = $client->request('GET', 'https://api.themoviedb.org/3/search/movie', [
                'query' => [
                    'api_key' => $tmdbApiKey,
                    'query' => $title, // Movie title passed in the query
                ],
            ]);

            $tmdbData = json_decode($tmdbResponse->getBody(), true);
            $movie = $tmdbData['results'][0] ?? null;

            if (!$movie) {
                return response()->json(['error' => 'Movie not found'], 404);
            }

            $movieId = $movie['id'];

            // Step 2: Fetch movie details (additional information)
            $detailsResponse = $client->request('GET', "https://api.themoviedb.org/3/movie/{$movieId}", [
                'query' => [
                    'api_key' => $tmdbApiKey,
                ],
            ]);

            $detailsData = json_decode($detailsResponse->getBody(), true);

            // Step 3: Build the response with movie details
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
                ]
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => 'API request failed', 'details' => $e->getMessage()], 500);
        }
    }
}
