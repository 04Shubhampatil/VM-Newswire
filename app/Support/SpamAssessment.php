<?php

namespace App\Support;

/**
 * Result of App\Services\SpamDetector::assess(): the score and which rules matched.
 */
final class SpamAssessment
{
    /**
     * @param  array<int, array{rule: string, points: int, label: string}>  $reasons
     */
    public function __construct(
        public readonly int $score,
        public readonly array $reasons,
        private readonly int $spamThreshold,
        private readonly int $reviewThreshold,
    ) {}

    public function isSpam(): bool
    {
        return $this->score >= $this->spamThreshold;
    }

    /** Medium risk: kept as a normal enquiry, flagged for the team to look at. */
    public function needsReview(): bool
    {
        return ! $this->isSpam() && $this->score >= $this->reviewThreshold;
    }

    /** @return array<int, string> */
    public function rules(): array
    {
        return array_column($this->reasons, 'rule');
    }
}
