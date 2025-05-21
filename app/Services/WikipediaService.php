<?php
namespace App\Services;

use GuzzleHttp\Client;

class WikipediaService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://en.wikipedia.org/api/rest_v1/',
            'timeout' => 5.0,
        ]);
    }

    public function getSummary(string $title)
{
    try {
        // Replace spaces with underscores before encoding
        $titleForApi = str_replace(' ', '_', $title);

        $response = $this->client->get('page/summary/' . urlencode($titleForApi));
        $data = json_decode($response->getBody(), true);

        if (isset($data['type']) && $data['type'] === 'https://mediawiki.org/wiki/HyperSwitch/errors/not_found') {
            return [];
        }

        return [
            'title' => $data['title'] ?? null,
            'extract' => $data['extract'] ?? null,
            'thumbnail' => $data['thumbnail']['source'] ?? null,
            'page_url' => $data['content_urls']['desktop']['page'] ?? null,
        ];
    } catch (\Exception $e) {
        return [];
    }
}

}
