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
    protected $signature = 'wishlist:export {--f|filename=export} {--d|dir=} {--H|header}';

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
        $filename = $this->option('filename');
        $d = (new \DateTime)->format('YmdHis');
        if (!Str::endsWith($filename, '.csv')) {
            $filename .= sprintf("_%s.csv", $d);
        } else {
            $filename = Str::replaceLast('.csv', sprintf("_%s.csv", $d), $filename);
        }

        $directory = $this->option('dir') ? $this->Option('dir') : "/tmp";
        $filepath = sprintf("%s/%s", $directory, $filename);
        $this->info(sprintf("Exporting file to: %s", $filepath));
        
        $w = new Wishlist();
        if (!$w->getReport($filepath, $this->option('header'))) {
            $this->error('Error in creating the file, see log for more info');
        } else {
            $this->info('File succesfully exported');
        }
    }
}