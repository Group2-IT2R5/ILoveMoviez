<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReviewService;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index()
    {
        $reviews = $this->reviewService->getAllReviews();
        return response()->json(['data' => $reviews]);
    }

    public function store(Request $request, $movieTitle)
    {
        try {
            $result = $this->reviewService->createReview(auth()->id(), $movieTitle, $request->all());

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 403);
            }

            return response()->json(['message' => 'Review added', 'data' => $result['review']], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function update(Request $request, $movieTitle)
    {
        try {
            $result = $this->reviewService->updateReview(auth()->id(), $movieTitle, $request->all());

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 404);
            }

            return response()->json(['message' => 'Review updated', 'data' => $result['review']]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function destroy($movieTitle)
    {
        $result = $this->reviewService->deleteReview(auth()->id(), $movieTitle);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 404);
        }

        return response()->json(['message' => $result['message']]);
    }
}
