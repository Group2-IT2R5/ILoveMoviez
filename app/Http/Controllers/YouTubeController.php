<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class YouTubeController extends Controller
{
    public function searchMovie(Request $request)
    {
        // Get the search term from the query parameter (e.g., the movie name)
        $query = $request->input('query');

        // If no query is provided, return an error
        if (!$query) {
            return response()->json(['error' => 'Query parameter is required.'], 400);
        }

        // Get the current year for filtering movies from this year
        $currentYear = date('Y');

        // Set the "publishedAfter" date to the beginning of the current year (January 1st)
        $publishedAfter = "$currentYear-01-01T00:00:00Z";

        // API key and client setup
        $apiKey = env('YOUTUBE_API_KEY');
        $client = new Client();

        // Make the API request to YouTube with the dynamic query and publishedAfter filter
        $response = $client->get('https://www.googleapis.com/youtube/v3/search', [
            'query' => [
                'part' => 'snippet',
                'q' => $query,  // Use the query parameter as is (no "trailer" appended)
                'key' => $apiKey,
                'maxResults' => 5,  // Limit to 5 results (can be adjusted)
                'type' => 'video',
                'publishedAfter' => $publishedAfter,  // Filter by this year
            ]
        ]);

        // Parse the response and return video URLs
        $data = json_decode($response->getBody()->getContents(), true);

        // If videos are found, return the results
        if (isset($data['items']) && count($data['items']) > 0) {
            $videos = [];
            foreach ($data['items'] as $item) {
                $videos[] = [
                    'title' => $item['snippet']['title'],
                    'video_url' => "https://www.youtube.com/watch?v=" . $item['id']['videoId']
                ];
            }
            return response()->json($videos);
        }

        // If no videos are found, return an error
        return response()->json(['error' => 'No movies found for this year'], 404);
    }
}
