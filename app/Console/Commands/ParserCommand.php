<?php

namespace App\Console\Commands;

use App\Jobs\GenerateFilesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ParserCommand extends Command
{
    protected $signature = 'parse:file';
    protected $description = 'Command description';

    public function handle()
    {
        $data = File::get('parser/data.json');
        collect(json_decode($data, true))->each(function ($dataSet) {
            GenerateFilesJob::dispatch($dataSet);
        });
    }
}
