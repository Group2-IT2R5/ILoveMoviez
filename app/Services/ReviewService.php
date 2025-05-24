<?php

namespace App\Services;

use App\Models\Review;
use App\Services\TMDBService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    protected $tmdb;

    public function __construct(TMDBService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function getAllReviews()
    {
        return Review::all();
    }

    public function createReview($userId, $movieTitle, $data)
    {
        $validator = Validator::make($data, [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $movie = $this->tmdb->getMovieDetails($movieTitle);

        if (isset($movie['error'])) {
            return ['error' => 'Movie not found in TMDb'];
        }

        $existingReview = Review::where('user_id', $userId)
                                ->where('movie_title', $movie['title'])
                                ->first();

        if ($existingReview) {
            return ['error' => 'You have already reviewed this movie'];
        }

        $review = Review::create([
            'user_id'     => $userId,
            'movie_title' => $movie['title'],
            'rating'      => $data['rating'],
            'review'      => $data['review'],
        ]);

        return ['review' => $review];
    }

    public function updateReview($userId, $movieTitle, $data)
    {
        $validator = Validator::make($data, [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $movie = $this->tmdb->getMovieDetails($movieTitle);

        if (isset($movie['error'])) {
            return ['error' => 'Movie not found in TMDb'];
        }

        $review = Review::where('user_id', $userId)
                        ->where('movie_title', $movie['title'])
                        ->first();

        if (!$review) {
            return ['error' => 'Review not found'];
        }

        $review->rating = $data['rating'];
        $review->review = $data['review'];
        $review->save();

        return ['review' => $review];
    }

    public function deleteReview($userId, $movieTitle)
    {
        $movie = $this->tmdb->getMovieDetails($movieTitle);

        if (isset($movie['error'])) {
            return ['error' => 'Movie not found in TMDb'];
        }

        $review = Review::where('user_id', $userId)
                        ->where('movie_title', $movie['title'])
                        ->first();

        if (!$review) {
            return ['error' => 'Review not found'];
        }

        $review->delete();

        return ['message' => 'Review deleted'];
    }
}
