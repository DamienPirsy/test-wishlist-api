<?php

namespace App\Console\Commands;

use App\Wishlist;
use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Str;

class ExportCommand extends Command
{
    /**
     * 
     *
     * @var string
     */
    protected $signature = 'wishlist:export {--o|output=export}';

    /**
     * 
     *
     * @var string
     */
    protected $description = 'Wishlist exporting tool. Options: -o filename';

    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * CSV file format: user;title wishlist;number of items
     *
     * @return mixed
     */
    public function handle()
    {
        // Mi costruisco il nome del file di output
        $outputFile = $this->option('output');
        $d = (new \DateTime)->format('YmdHis');
        if (!Str::endsWith($outputFile, '.csv')) {
            $outputFile .= sprintf("_%s.csv", $d);
        } else {
            $outputFile = Str::replaceLast('.csv', sprintf("_%s.csv", $d), $outputFile);
        }

        $w = new Wishlist();
        $w->getReport($outputFile);
    }
}