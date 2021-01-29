<?php

namespace Tipoff\Refunds\Commands;

use Illuminate\Console\Command;

class RefundsCommand extends Command
{
    public $signature = 'refunds';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
