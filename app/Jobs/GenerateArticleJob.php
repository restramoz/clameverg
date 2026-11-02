<?php

namespace App\Jobs;

use App\Models\ArticleGenerationJob;
use App\Services\OllamaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 1200; // Allow multi-round continuation (8 x 120s) plus overhead

    public string $jobId;

    public array $params;

    public function __construct(string $jobId, array $params)
    {
        $this->jobId = $jobId;
        $this->params = $params;
    }

    public function handle(OllamaService $ollama): void
    {
        $job = ArticleGenerationJob::find($this->jobId);
        if (! $job) {
            return;
        }

        // Prevent duplicate execution if job is already completed/failed
        if (in_array($job->status, ['completed', 'failed'])) {
            return;
        }

        $job->update(['status' => 'running']);

        try {
            $result = $ollama->generateArticle($this->params);

            if (! $result['success']) {
                $job->update([
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'Gagal menghasilkan artikel.',
                ]);

                return;
            }

            $job->update([
                'status' => 'completed',
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('GenerateArticleJob failed', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
            ]);

            $job->update([
                'status' => 'failed',
                'error' => 'Terjadi kesalahan saat menghasilkan artikel. Silakan coba lagi.',
            ]);
        }
    }
}