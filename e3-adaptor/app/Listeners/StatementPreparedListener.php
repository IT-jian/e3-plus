<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use Illuminate\Database\Events\StatementPrepared;

class StatementPreparedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(StatementPrepared $event)
    {
        // 切换返回模式为数组
        // $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
    }
}
