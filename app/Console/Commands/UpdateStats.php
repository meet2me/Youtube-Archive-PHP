<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateStats extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'update:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all channel stats';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->info('- Starting Update...');

        $channels = \App\Channel::all();

        foreach ($channels as $chan) {
          $chan = \App\Channel::where('id', $chan->id)
                        ->first();

          $this->info('= Updating: ' . $chan->Title . ' (' . $chan->YT_ID . ')');

          $chan->stats_dl = \App\Video::where('Chan_ID', $chan->id)
                          ->where('File_Status', 'Saved!')
                          ->count();

          $this->info('[' . $chan->Title . '](File) Downloaded:' . $chan->stats_dl);

          $chan->stats_nodl = \App\Video::where('Chan_ID', $chan->id)
                          ->where('File_Status', '<>', 'Saved!')
                          ->count();

          $this->info('[' . $chan->Title . '](File) Not Downloaded:' . $chan->stats_nodl);

          $chan->stats_v_pub = \App\Video::where('Chan_ID', $chan->id)
                          ->where('YT_Status', 'public')
                          ->count();

          $this->info('[' . $chan->Title . '](Videos) Public:' . $chan->stats_v_pub);

          $chan->stats_v_unlisted = \App\Video::where('Chan_ID', $chan->id)
                          ->where('YT_Status', 'unlisted')
                          ->count();

          $this->info('[' . $chan->Title . '](Videos) Unlisted:' . $chan->stats_v_unlisted);

          $chan->stats_v_notfound = \App\Video::where('Chan_ID', $chan->id)
                          ->where('YT_Status', 'not found')
                          ->count();

          $this->info('[' . $chan->Title . '](Videos) Private:' . $chan->stats_v_notfound);

          $chan->save();
        }

        $this->info('- Updating Done!');
    }

}
