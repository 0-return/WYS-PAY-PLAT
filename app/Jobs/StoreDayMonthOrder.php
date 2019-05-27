<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreDayMonthOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $store_id;//
    protected $user_id;//
    protected $merchant_id;//
    protected $total_amount;//
    protected $ways_type;
    protected $ways_source;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_id, $user_id, $merchant_id, $total_amount, $ways_type, $ways_source)
    {
        //

        $this->store_id = $store_id;
        $this->user_id = $user_id;
        $this->merchant_id = $merchant_id;
        $this->total_amount = $total_amount;
        $this->ways_type = $ways_type;
        $this->ways_source = $ways_source;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        \App\Common\StoreDayMonthOrder::insert($this->store_id, $this->user_id, $this->merchant_id, $this->total_amount, $this->ways_type, $this->ways_source);

    }
}
