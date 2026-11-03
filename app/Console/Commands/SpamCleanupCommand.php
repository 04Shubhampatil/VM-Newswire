<?php

namespace App\Console\Commands;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use App\Services\SpamDetector;
use Illuminate\Console\Command;

/**
 * Re-scores existing "New" enquiries with the spam rules and moves the ones at or above the spam
 * threshold to the Spam status. Dry run by default; pass --apply to change anything.
 *
 * Safe by design: never deletes, never touches Contacted/Closed enquiries (the team has worked on
 * those), records who moved each one ("cleanup") and can be undone from the admin Spam tab.
 */
class SpamCleanupCommand extends Command
{
    protected $signature = 'enquiries:spam-cleanup
                            {--apply : Actually change statuses (without it, only a report is shown)}
                            {--sample=10 : How many example enquiries to list}';

    protected $description = 'Move existing spam enquiries (score ≥ threshold) from New to Spam. Dry run unless --apply.';

    public function handle(SpamDetector $detector): int
    {
        $apply = (bool) $this->option('apply');
        $sampleSize = max(0, (int) $this->option('sample'));
        $threshold = (int) config('spam.spam_threshold');

        $this->info(($apply ? 'APPLYING' : 'DRY RUN (nothing will change)')." — spam threshold {$threshold}, review threshold ".config('spam.review_threshold'));

        $toSpam = 0;
        $review = 0;
        $clean = 0;
        $byRule = [];
        $samples = [];

        // Skips enquiries an admin already restored from Spam ("not spam" is their decision).
        Enquiry::query()->where('status', EnquiryStatus::New)
            ->where(fn ($q) => $q->whereNull('spam_marked_by')->orWhere('spam_marked_by', '!=', Enquiry::NOT_SPAM))
            ->orderBy('id')->lazyById(200)
            ->each(function (Enquiry $enquiry) use ($detector, $apply, &$toSpam, &$review, &$clean, &$byRule, &$samples, $sampleSize) {
                $assessment = $detector->assess(
                    ['name' => $enquiry->name, 'email' => $enquiry->email, 'company' => $enquiry->company, 'message' => $enquiry->message],
                    ['ignore_id' => $enquiry->id],
                );

                if ($assessment->isSpam()) {
                    $toSpam++;
                    foreach ($assessment->rules() as $rule) {
                        $byRule[$rule] = ($byRule[$rule] ?? 0) + 1;
                    }
                    if (count($samples) < $sampleSize) {
                        $samples[] = [$enquiry->id, mb_strimwidth($enquiry->name, 0, 40, '…'), $enquiry->email, $assessment->score, implode(', ', $assessment->rules())];
                    }
                } elseif ($assessment->needsReview()) {
                    $review++;
                } else {
                    $clean++;
                }

                if ($apply) {
                    $enquiry->forceFill([
                        'spam_score' => $assessment->score,
                        'spam_reasons' => $assessment->reasons ?: null,
                    ]);
                    if ($assessment->isSpam()) {
                        $enquiry->forceFill([
                            'status' => EnquiryStatus::Spam,
                            'spam_marked_by' => 'cleanup',
                            'spam_marked_at' => now(),
                        ]);
                    }
                    $enquiry->save();
                }
            });

        $this->newLine();
        $this->table(['Result', 'Enquiries'], [
            [$apply ? 'Moved to Spam' : 'Would move to Spam', $toSpam],
            ['Kept as New, flagged "Possible spam"', $review],
            ['Kept as New (clean)', $clean],
        ]);

        if ($byRule) {
            arsort($byRule);
            $this->table(['Rule matched (spam only)', 'Enquiries'], collect($byRule)->map(fn ($n, $rule) => [$rule, $n])->values()->all());
        }

        if ($samples) {
            $this->table(['ID', 'Name', 'Email', 'Score', 'Rules'], $samples);
        }

        $this->line($apply
            ? 'Done. Nothing was deleted; restore any enquiry from Admin → Enquiries → Spam.'
            : 'Dry run only. Run again with --apply to make these changes.');

        return self::SUCCESS;
    }
}
