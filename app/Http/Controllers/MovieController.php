<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TMDBService;
use App\Services\YouTubeService;
use App\Services\GiphyService;
use App\Services\SpotifyService;
use App\Models\Review;

class MovieController extends Controller
{
    protected $tmdb, $yt, $giphy, $spotify;

    public function __construct(
        TMDBService $tmdb,
        YouTubeService $yt,
        GiphyService $giphy,
        SpotifyService $spotify
    ) {
        $this->tmdb = $tmdb;
        $this->yt = $yt;
        $this->giphy = $giphy;
        $this->spotify = $spotify;
    }

public function search(Request $request)
{
    $title = $request->query('title');

    if (!$title) {
        return response()->json(['error' => 'Title is required'], 400);
    }

    try {
        $movieData = $this->tmdb->getMovieDetails($title);

        if (isset($movieData['error'])) {
            return response()->json(['error' => 'Movie not found in TMDb'], 404);
        }

        
        $reviews = Review::where('movie_title', $movieData['title'])
            ->get(['id', 'user_id', 'rating', 'review', 'created_at']);

        return response()->json([
            'movie'   => $movieData,
            'trailer' => $this->yt->getTrailer($title),
            'giphy'   => $this->giphy->getGif($title),
            'spotify' => $this->spotify->getPlaylist($title),
            'ILoveMoviez users reviews' => $reviews,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error'   => 'Something went wrong',
            'details' => $e->getMessage(),
        ], 500);
    }
}


}
