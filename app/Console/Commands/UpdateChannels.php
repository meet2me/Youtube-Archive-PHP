<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateChannels extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'update:channels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all channels & get new uploads';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->info('- Starting Channel Update...');

        $channels = \App\Channel::all();

        foreach ($channels as $chan) {
          $this->info('= Updating: ' . $chan->Title . ' (' . $chan->YT_ID . ')');

          $playlistid = $chan->UploadPlaylistID;

          $this->__updateVideoPage($chan->YT_ID, $chan->id, $playlistid);
        }

        $this->info('- Channel Updating Done!');
    }

    // Update Videos
    public function __updateVideoPage($chanid, $chan_dbid, $playlistid)
    {
      $raw = file_get_contents("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,status&maxResults=50&playlistId=" . $playlistid . "&key=" . env('YT_API_KEY'));

      $json = json_decode($raw);

      $end = false;
      if(!isset($json->nextPageToken))
      {
        $end = true;
      }else
      {
        $nextpagetoken = $json->nextPageToken;
      }

      foreach ($json->items as $vid) {
        $this->__processVideo($chan_dbid, $vid);
      }

      if($end) {
        return;
      }else {
        $this->__updateVideoNextPage($chanid, $chan_dbid, $playlistid, $nextpagetoken);
      }
    }

    public function __updateVideoNextPage($chanid, $chan_dbid, $playlistid, $nextpagetoken)
    {
      $raw = file_get_contents("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,status&maxResults=50&playlistId=" . $playlistid ."&pageToken=" . $nextpagetoken . "&key=" . env('YT_API_KEY'));

      $json = json_decode($raw);

      $end = false;
      if(!isset($json->nextPageToken))
      {
        $end = true;
      }else
      {
        $nextpagetoken = $json->nextPageToken;
      }

      foreach ($json->items as $vid) {
        $this->__processVideo($chan_dbid, $vid);
      }

      if($end) {
        return;
      }else {
        $this->__updateVideoNextPage($chanid, $chan_dbid, $playlistid, $nextpagetoken);
      }
    }

    public function __processVideo($chanid, $json)
    {
      $vid_check = \App\Video::where('YT_ID', $json->snippet->resourceId->videoId)->first();

      // Video not exist. So create!
      if (count($vid_check) == 0){
        // Create new Video
        $v = new \App\Video;
        $v->YT_ID = $json->snippet->resourceId->videoId;
        $v->Chan_ID = $chanid;
        $v->Title = $json->snippet->title;
        $v->Description = $json->snippet->description;
        $v->YT_Status = $json->status->privacyStatus;
        $v->Upload_Date = $this->__convertDate($json->snippet->publishedAt);
        $v->save();

        // Grab video instantly to get the ID in future calls
        $vid_check = \App\Video::where('YT_ID', $json->snippet->resourceId->videoId)->first();

        // Add first change log
        $vcl = new \App\VideoChangeLog;
        $vcl->Video_ID = $vid_check->id;
        $vcl->Title = $json->snippet->title;
        $vcl->Description = $json->snippet->description;
        $vcl->YT_Status = $json->status->privacyStatus;
        $vcl->save();
      }else
      {
        // Find and update all video change logs
        // TITLE
        if($vid_check->Title != $json->snippet->title)
        {
          $vcl_title_new = new \App\VideoChangeLog;
          $vcl_title_new->Video_ID = $vid_check->id;
          $vcl_title_new->Title = $json->snippet->title;
          $vcl_title_new->save();

          $vid_check->Title = $json->snippet->title;
        }

        // DESCRIPTION
        if($vid_check->Description != $json->snippet->description)
        {
          $vcl_desc_new = new \App\VideoChangeLog;
          $vcl_desc_new->Video_ID = $vid_check->id;
          $vcl_desc_new->Description = $json->snippet->description;
          $vcl_desc_new->save();

          $vid_check->Description = $json->snippet->description;
        }

        // YT STATUS
        if($vid_check->YT_Status != $json->status->privacyStatus)
        {
          $vcl_status_new = new \App\VideoChangeLog;
          $vcl_status_new->Video_ID = $vid_check->id;
          $vcl_status_new->YT_Status = $json->status->privacyStatus;
          $vcl_status_new->save();

          $vid_check->YT_Status = $json->status->privacyStatus;
        }

        $vid_check->save();
      }

      $this->info('(' . $json->snippet->channelTitle . ') ' . $vid_check->Title);
    }

    public function __convertDate($str)
    {
      return new \DateTime($str);DateTime::createFromFormat(DateTime::ISO8601, $str);
    }
}
