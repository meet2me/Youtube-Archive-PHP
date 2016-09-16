<?php

namespace App\Http\Controllers;

use DB;

use \App\Video;
use \App\Jobs\DownloadVideoJob;

class ChanController extends Controller
{
    public function index($id)
    {
      $chan = \App\Channel::where('YT_ID', $id)
                  ->first();

      if(count($chan) == 0)
      {
        return "Chan not found!";
      }

      $videos = \App\Video::where('Chan_ID', $chan->id)
                  ->orderBy('Upload_Date', 'desc')
                  ->get();

      return view('chan', [
        'videos' => $videos,
        'chan' => $chan,
        'id' => $chan->YT_ID,
        'disk' => $this->__getDiskInfo(),
        'queue' => $this->__getQueuedDownloads(),
        'queued_ids' => $this->__getQueuedDownloadsIDs(),
      ]);
    }

    public function updatePlaylist($YT_ID)
    {
      $chan = \App\Channel::where('YT_ID', $YT_ID)->first();

      $playlistid = $chan->UploadPlaylistID;

      $this->__updateVideoPage($YT_ID, $chan->id, $playlistid);

      return redirect()->route('chan', ['id' => $YT_ID]);
    }

    public function downloadVideo($chan_yt_id, $db_video_id)
    {
      \Queue::push(new DownloadVideoJob($chan_yt_id, $db_video_id));

      return redirect()->route('chan', ['id' => $chan_yt_id]);
    }

    public function downloadVideoSilent($chan_yt_id, $db_video_id)
    {
      \Queue::push(new DownloadVideoJob($chan_yt_id, $db_video_id));

      return null;
    }

    public function updateVideo($chid, $vid)
    {
      $this->__ProcessSeperateVideo($vid);

      return redirect()->route('chan', ['id' => $chid]);
    }

    public function updateVideos($chid)
    {
      $chan = \App\Channel::where('YT_ID', $chid)->first();

      if(count($chan) == 0)
      {
        return "Channel not found!";
      }

      $videos = \App\Video::where('Chan_ID', $chan->id)->get();

      foreach ($videos as $video) {
        $this->__ProcessSeperateVideo($video->YT_ID);
      }

      return redirect()->route('chan', ['id' => $chid]);
    }

    public function downloadAll($chid)
    {
      $chan = \App\Channel::where('YT_ID', $chid)->first();

      if(count($chan) == 0)
      {
        return "Channel not found!";
      }

      $videos = \App\Video::where('Chan_ID', $chan->id)
                      ->orWhereNull('File_Status', '!=', 'Saved!')
                      ->get();

      foreach ($videos as $video) {
        \Queue::push(new DownloadVideoJob($chan->YT_ID, (string)$video->id));
      }

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
    }
}
