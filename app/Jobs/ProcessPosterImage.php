<?php

namespace App\Jobs;

use App\Models\Concert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPosterImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Concert $concert;

    public function __construct(Concert $concert)
    {
        $this->concert = $concert;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // resize image
    }
}
