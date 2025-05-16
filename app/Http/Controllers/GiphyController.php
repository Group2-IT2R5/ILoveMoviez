<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class GiphyController extends Controller
{
    public function search(Request $request)
    {
        $title = $request->query('title');

        if (!$title) {
            return response()->json(['error' => 'Title is required'], 400);
        }

        // Load the Giphy API key
        $giphyApiKey = env('GIPHY_API_KEY');

        if (!$giphyApiKey) {
            return response()->json(['error' => 'Giphy API key is not set'], 500);
        }

        $client = new Client();

        try {
            // Step 1: Search for GIFs on Giphy
            $giphyResponse = $client->request('GET', 'https://api.giphy.com/v1/gifs/search', [
                'query' => [
                    'api_key' => $giphyApiKey,
                    'q' => $title, // Use movie title for the search
                    'limit' => 1, // Limit to 1 GIF
                ],
            ]);

            $giphyData = json_decode($giphyResponse->getBody(), true);
            $giphyGif = $giphyData['data'][0]['images']['original']['url'] ?? null;

            if (!$giphyGif) {
                return response()->json(['error' => 'No GIF found'], 404);
            }

            return response()->json([
                'gif_url' => $giphyGif
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'API request failed', 'details' => $e->getMessage()], 500);
        }
    }
}
