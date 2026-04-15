<?php

namespace App\Services;

class ContentModerationService
{
    /**
     * @return array<int, string>
     */
    public function findBlockedTerms(string $content): array
    {
        $normalizedContent = $this->normalize($content);
        if ($normalizedContent === '') {
            return [];
        }

        $blockedTerms = config('moderation.blocked_terms', []);
        if (! is_array($blockedTerms) || $blockedTerms === []) {
            return [];
        }

        $matches = [];

        foreach ($blockedTerms as $term) {
            $term = trim((string) $term);
            if ($term === '') {
                continue;
            }

            $normalizedTerm = $this->normalize($term);
            if ($normalizedTerm === '') {
                continue;
            }

            if ($this->containsWholeTerm($normalizedContent, $normalizedTerm)) {
                $matches[] = $normalizedTerm;
            }
        }

        return array_values(array_unique($matches));
    }

    private function containsWholeTerm(string $content, string $term): bool
    {
        $pattern = '/(^|\s)'.preg_quote($term, '/').'(\s|$)/i';

        return (bool) preg_match($pattern, $content);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value);
    }
}
