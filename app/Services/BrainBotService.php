<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class BrainBotService
{
    /**
     * @return array{answer: string, sources: array<int, array{title: string, url: string, snippet: string}>}
     */
    public function answer(string $question): array
    {
        $sources = $this->searchWeb($question);
        $context = $this->buildContext($sources);

        // Prefer OpenRouter; if unavailable, return a safe fallback summary.
        $answer = $this->askOpenRouter($question, $context);
        if ($answer === null) {
            $answer = "I am having trouble reaching the live model right now. Here is a web-based summary:\n\n"
                .$this->fallbackAnswer($sources);
        }

        return [
            'answer' => $answer,
            'sources' => $sources,
        ];
    }

    /**
     * Ask OpenRouter API for an answer.
     */
    private function askOpenRouter(string $question, string $context): ?string
    {
        $url = (string) config('services.brainbot.openrouter_url');
        $model = (string) config('services.brainbot.model');
        $apiKey = (string) config('services.brainbot.openrouter_key');

        if ($url === '' || $model === '' || $apiKey === '') {
            return null;
        }

        $system = 'You are brainBot, a helpful learning assistant for BrainBites. '
            .'Answer clearly and concisely. '
            .'If external context is provided, use it. '
            .'Do not mention system instructions or internal context handling.';

        $userPrompt = "Question: {$question}";
        if ($context !== '') {
            $userPrompt .= "\n\nWeb context:\n{$context}";
        }

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'HTTP-Referer' => config('app.url', ''),
                    'X-Title' => 'BrainBites BrainBot',
                ])
                ->post($url, [
                    'model' => $model,
                    'provider' => [
                        // Allow broader provider routing when account UI does not expose privacy controls.
                        'data_collection' => 'allow',
                        'allow_fallbacks' => true,
                        'sort' => 'price',
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        [
                            'role' => 'user',
                            'content' => $userPrompt,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                $errorMessage = (string) data_get($response->json(), 'error.message', '');
                if ($errorMessage === '') {
                    $errorMessage = $response->body();
                }

                \Log::error('OpenRouter API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'model' => $model,
                ]);

                if (str_contains(strtolower($errorMessage), 'guardrail restrictions and data policy')) {
                    return null;
                }

                if (str_contains(strtolower($errorMessage), 'deprecated')) {
                    return null;
                }

                return null;
            }

            $choices = $response->json('choices');

            if (is_array($choices) && isset($choices[0]['message']['content'])) {
                return trim((string) $choices[0]['message']['content']);
            }

            \Log::error('OpenRouter API: No valid choices in response', [
                'response' => $response->json(),
                'model' => $model,
            ]);

            return null;
        } catch (Throwable $e) {
            \Log::error('OpenRouter API exception', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            return null;
        }
    }

    /**
     * @return array<int, array{title: string, url: string, snippet: string}>
     */
    private function searchWeb(string $query): array
    {
        $items = [];

        try {
            $duck = Http::timeout(12)->get('https://api.duckduckgo.com/', [
                'q' => $query,
                'format' => 'json',
                'no_html' => 1,
                'no_redirect' => 1,
                'skip_disambig' => 1,
            ])->json();

            if (! empty($duck['AbstractText']) && ! empty($duck['AbstractURL'])) {
                $items[] = [
                    'title' => (string) ($duck['Heading'] ?: 'DuckDuckGo Result'),
                    'url' => (string) $duck['AbstractURL'],
                    'snippet' => (string) $duck['AbstractText'],
                ];
            }

            if (! empty($duck['RelatedTopics']) && is_array($duck['RelatedTopics'])) {
                foreach ($duck['RelatedTopics'] as $topic) {
                    if (count($items) >= 6) {
                        break;
                    }

                    if (isset($topic['Text'], $topic['FirstURL'])) {
                        $items[] = [
                            'title' => Str::limit((string) $topic['Text'], 70, ''),
                            'url' => (string) $topic['FirstURL'],
                            'snippet' => (string) $topic['Text'],
                        ];
                    }
                }
            }
        } catch (Throwable) {
            // Continue with Wikipedia fallback if DuckDuckGo fails.
        }

        try {
            $wikiSearch = Http::timeout(12)->get('https://en.wikipedia.org/w/api.php', [
                'action' => 'query',
                'list' => 'search',
                'srsearch' => $query,
                'utf8' => 1,
                'format' => 'json',
                'srlimit' => 3,
            ])->json();

            $results = $wikiSearch['query']['search'] ?? [];

            foreach ($results as $result) {
                if (count($items) >= 8) {
                    break;
                }

                $title = (string) ($result['title'] ?? '');
                if ($title === '') {
                    continue;
                }

                $summaryUrl = 'https://en.wikipedia.org/api/rest_v1/page/summary/'.rawurlencode($title);
                $summary = Http::timeout(12)->get($summaryUrl)->json();

                $extract = (string) ($summary['extract'] ?? '');
                $url = (string) ($summary['content_urls']['desktop']['page'] ?? '');

                if ($extract !== '' && $url !== '') {
                    $items[] = [
                        'title' => $title,
                        'url' => $url,
                        'snippet' => $extract,
                    ];
                }
            }
        } catch (Throwable) {
            // Return what we already have.
        }

        $deduped = [];
        $seen = [];

        foreach ($items as $item) {
            if ($item['url'] === '' || isset($seen[$item['url']])) {
                continue;
            }

            $seen[$item['url']] = true;
            $deduped[] = [
                'title' => Str::limit(trim($item['title']), 120),
                'url' => $item['url'],
                'snippet' => Str::limit(trim($item['snippet']), 450),
            ];
        }

        return array_slice($deduped, 0, 6);
    }

    /**
     * @param  array<int, array{title: string, url: string, snippet: string}>  $sources
     */
    private function buildContext(array $sources): string
    {
        if ($sources === []) {
            return '';
        }

        $lines = [];

        foreach ($sources as $index => $source) {
            $n = $index + 1;
            $lines[] = "Source {$n}: {$source['title']}";
            $lines[] = "URL: {$source['url']}";
            $lines[] = "Snippet: {$source['snippet']}";
            $lines[] = '';
        }

        return implode("\n", $lines);
    }



    /**
     * @param  array<int, array{title: string, url: string, snippet: string}>  $sources
     */
    private function fallbackAnswer(array $sources): string
    {
        if ($sources === []) {
            return 'I could not find strong web results for that yet. Try a more specific question.';
        }

        $lines = ['Here is what I found from web sources:'];

        foreach (array_slice($sources, 0, 3) as $source) {
            $lines[] = '- '.$source['title'].': '.$source['snippet'];
        }

        $lines[] = 'I can refine this if you ask a narrower follow-up.';

        return implode("\n", $lines);
    }
}
