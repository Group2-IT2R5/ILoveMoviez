<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Services\WikipediaService;

class TMDBService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;
    protected $imageBase;
    protected $wikipediaService;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('TMDB_API_KEY');
        $this->baseUrl = env('TMDB_BASE_URL');
        $this->imageBase = env('TMDB_IMAGE_BASE');
        $this->wikipediaService = new WikipediaService();
    }

    public function getMovieDetails($title)
    {
        $response = $this->client->get("{$this->baseUrl}/search/movie", [
            'query' => [
                'api_key' => $this->apiKey,
                'query'   => $title,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $movie = $data['results'][0] ?? null;

        if (!$movie) {
            return ['error' => 'Movie not found'];
        }

        $credits = $this->getCredits($movie['id']);
        $similarMovies = $this->getSimilarMovies($movie['id']);
        $wikiMovie = $this->wikipediaService->getSummary($movie['title']);

        return [
            'title'        => $movie['title'] ?? 'N/A',
            'overview'     => $movie['overview'] ?? 'N/A',
            'release_date' => $movie['release_date'] ?? 'N/A',
            'rating'       => $movie['vote_average'] ?? 'N/A',
            'language'     => $movie['original_language'] ?? 'N/A',
            'poster'       => $movie['poster_path']
                ? $this->imageBase . $movie['poster_path']
                : null,
            'cast'         => $credits,
            'similar_movies' => $similarMovies,
            'wikipedia'    => [
                'page_url'  => $wikiMovie['page_url'] ?? null,
                'thumbnail' => $wikiMovie['thumbnail'] ?? null,
            ],
        ];
    }

    private function getCredits($movieId)
    {
        $response = $this->client->get("{$this->baseUrl}/movie/{$movieId}/credits", [
            'query' => ['api_key' => $this->apiKey],
        ]);

        $creditsData = json_decode($response->getBody(), true);

        return collect($creditsData['cast'])->take(5)->map(function ($actor) {
            $wikiInfo = $this->wikipediaService->getSummary($actor['name']);

            return [
                'name'                 => $actor['name'],
                'character'           => $actor['character'],
                'wikipedia_page_url'  => $wikiInfo['page_url'] ?? null,
                'wikipedia_thumbnail' => $wikiInfo['thumbnail'] ?? null,
            ];
        })->toArray();
    }

    private function getSimilarMovies($movieId)
    {
        $response = $this->client->get("{$this->baseUrl}/movie/{$movieId}/similar", [
            'query' => [
                'api_key'  => $this->apiKey,
                'language' => 'en-US',
                'page'     => 1,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return collect($data['results'] ?? [])
            ->take(5)
            ->map(fn($movie) => [
                'id'           => $movie['id'],
                'title'        => $movie['title'],
                'overview'     => $movie['overview'],
                'release_date' => $movie['release_date'],
                'poster'       => $movie['poster_path']
                    ? $this->imageBase . $movie['poster_path']
                    : null,
            ])->toArray();
    }
}
