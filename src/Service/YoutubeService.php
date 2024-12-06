<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class YoutubeService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getLatestVideos(string $channelId): ?array
    {
        if (!$channelId) {
            return null;
        }

        $response = $this->httpClient->request('GET', 'https://www.youtube.com/feeds/videos.xml', [
            'query' => [
                'channel_id' => $channelId,
            ],
        ]);

        $data = $response->getContent();
        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);

        if (empty($xml->entry)) {
            return null;
        }

        $videos = [];
        foreach ($xml->entry as $entry) {
            $media = $entry->children('media', true)->group;

            $videos[] = [
                'id' => (string)$entry->id,
                'title' => (string)$entry->title,
                'link' => [
                    '@attributes' => [
                        'rel' => 'alternate',
                        'href' => (string)$entry->link['href']
                    ]
                ],
                'author' => [
                    'name' => (string)$entry->author->name,
                    'uri' => (string)$entry->author->uri
                ],
                'published' => (string)$entry->published,
                'updated' => (string)$entry->updated,
                'thumbnail' => (string)$media->thumbnail->attributes()->url,
                'views' => (string)$media->community->statistics->attributes()->views,
                'description' => (string)$media->description
            ];
        }

        return $videos;
    }

    public function getChannelId(string $channelHandle): ?string
    {
        $response = $this->httpClient->request('GET', 'https://www.googleapis.com/youtube/v3/channels', [
            'query' => [
                'part' => 'id',
                'forHandle' => $channelHandle,
                'key' => $this->apiKey,
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['items'])) {
            return null;
        }

        return $data['items'][0]['id'] ?? null;
    }

    public function getChannelDetails(string $channelUrl): ?array
    {
        $channelUrl = rtrim($channelUrl, '/');

        if (preg_match('#/(@|c/|user/|channel/)([^/]+)#', $channelUrl, $matches)) {
            $prefix = $matches[1];
            $identifier = $matches[2];

            if ($prefix === 'c/' || $prefix === 'user/') {
                $channelId = $this->getChannelId($identifier);
                if (!$channelId) {
                    return null;
                }
            } else if ($prefix === '@') {
                $channelId = $this->getChannelId(ltrim($identifier, '@'));
                if (!$channelId) {
                    return null;
                }
            } else {
                $channelId = $identifier;
            }
        } else {
            $channelIdentifier = basename($channelUrl);
            if (str_starts_with($channelIdentifier, '@')) {
                $channelId = $this->getChannelId(ltrim($channelIdentifier, '@'));
                if (!$channelId) {
                    return null;
                }
            } else {
                $channelId = $channelIdentifier;
            }
        }

        $response = $this->httpClient->request('GET', 'https://www.googleapis.com/youtube/v3/channels', [
            'query' => [
                'part' => 'snippet',
                'id' => $channelId,
                'key' => $this->apiKey,
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['items'])) {
            return null;
        }

        return $data['items'][0] ?? null;
    }
}
