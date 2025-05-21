<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TMDBService;
use App\Services\YouTubeService;
use App\Services\GiphyService;
use App\Services\SpotifyService;

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
            return response()->json([
                'movie'   => $this->tmdb->getMovieDetails($title),
                'trailer' => $this->yt->getTrailer($title),
                'giphy'   => $this->giphy->getGif($title),
                'spotify' => $this->spotify->getPlaylist($title),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }
}
