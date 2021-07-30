<?php

namespace Azizyus\ImageManager\Commands;

use Azizyus\ImageManager\DB\Models\ManagedImage;
use Illuminate\Console\Command;

class GenerateVariations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imagemanager:generate:variations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates variations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $total = 0;
        for ($i=0;$i>=0;$i++)
        {
            $images = ManagedImage::skip(20*$i)->take(20)->get();
            $total+= $images->count();
            if($images->count())
                foreach ($images->all() as $item)
                {
                    imageManager()->maintainVariations($item->fileName);
                    $this->comment('generated file name: '.$item->fileName);
                }
            else
            {
                $this->comment('end of the images; '.$total.' image processed ');
                break;
            }
        }
        $this->comment('Completed variation generation');
        return 0;
    }
}
