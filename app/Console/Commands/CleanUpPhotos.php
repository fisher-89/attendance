<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanUpPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file:clean-up-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $date;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->date = date('Y/m/d', strtotime('-40 days'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $directory = 'photo/' . $this->date;
        $this->info($directory);
        $files = Storage::disk('upload')->allFiles($directory);
        foreach ($files as $file) {
            if (!preg_match('/thumb/', $file)) {
                Storage::disk('upload')->delete($file);
            }
        }
    }
}
