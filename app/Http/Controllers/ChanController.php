<?php

namespace App\Http\Controllers;

use DB;

use \App\Video;

class ChanController extends Controller
{
    public function index($id)
    {
      $chan = \App\Channel::where('YT_ID', $id)
                  ->get();

      $videos = \App\Video::where('Chan_ID', $chan[0]->id)
                  ->orderBy('Upload_Date', 'desc')
                  ->get();

      return view('chan', [
        'videos' => $videos,
        'id' => $id,
        'disk' => $this->__getDiskInfo(),
      ]);
    }

    public function updateVideos($YT_ID)
    {
      $chan = \App\Channel::where('YT_ID', $YT_ID)->get();

      $playlistid = $chan[0]->UploadPlaylistID;

      $this->__updateVideoPage($YT_ID, $chan[0]->id, $playlistid);

      return redirect()->route('chan', ['id' => $YT_ID]);
    }

    public function downloadVideo($id, $vid)
    {
      $vid_db = \App\Video::where('id', $vid)->get();

      $dlpath = env('DL_LOC') . '/' . $id;
      if(!file_exists($dlpath))
      {
        mkdir($dlpath);
      }

      //chdir($dlpath);
      exec('youtube-dl --write-sub --all-subs --write-description --write-info-json --write-annotations --write-thumbnail -f bestvideo[ext!=webm]+bestaudio[ext!=webm]/best[ext!=webm] -w -o "' . $dlpath . @"/%(id)s.%(ext)s" . '" https://www.youtube.com/watch?v=' . $vid_db[0]->YT_ID);

      $this->__processDownload($dlpath, $vid_db[0]->YT_ID);

      return redirect()->route('chan', ['id' => $id]);
    }

    public function updateVideo($chid, $vid)
    {
      app('App\Http\Controllers\HomeController')->__ProcessSeperateVideo($vid);

      return redirect()->route('chan', ['id' => $chid]);
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
        return redirect()->route('chan', ['id' => $chanid]);
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
        return redirect()->route('chan', ['id' => $chanid]);
      }else {
        $this->__updateVideoNextPage($chanid, $chan_dbid, $playlistid, $nextpagetoken);
      }
    }

    public function __processVideo($chanid, $json)
    {
      $vid_check = \App\Video::where('YT_ID', $json->snippet->resourceId->videoId)->get();

      // Video not exist. So create!
      if ($vid_check->isEmpty()){
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
    }

    // Download Video
    public function __processDownload($dlpath, $v_YTID)
    {
      $found_files = glob($dlpath . '/' . $v_YTID . '.mp4');

      if(count($found_files) == 0)
      {
        return "No videos found... Download must have failed!";
      }

      $vid_check = \App\Video::where('YT_ID', $v_YTID)->first();

      // Add first change log
      $vcl = new \App\VideoChangeLog;
      $vcl->Video_ID = $vid_check->id;
      $vcl->File_Status = "Saved!";
      $vcl->File_Name = $found_files[0];
      $vcl->save();

      $vid_check->File_Status = "Saved!";
      $vid_check->File_Name = $found_files[0];
      $vid_check->save();
    }

    public function __convertDate($str)
    {
      return new \DateTime($str);DateTime::createFromFormat(DateTime::ISO8601, $str);
    }
}
