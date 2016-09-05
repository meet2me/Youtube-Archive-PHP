<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __getDiskInfo()
    {
      return array(
        'free' => disk_total_space(env('DL_LOC')) - disk_free_space(env('DL_LOC')),
        'total' => disk_total_space(env('DL_LOC')),
        'format_free' => $this->__humanSize(disk_total_space(env('DL_LOC')) - disk_free_space(env('DL_LOC'))),
        'format_total' => $this->__humanSize(disk_total_space(env('DL_LOC'))),
      );
    }

    // Credit: http://php.net/manual/en/function.disk-total-space.php
    public function __humanSize($Bytes)
    {
      $Type=array("", "KB", "MB", "GB", "TB", "PB");
      $Index=0;
      while($Bytes>=1024)
      {
        $Bytes/=1024;
        $Index++;
      }
      return("".round($Bytes, 2)." ".$Type[$Index]);
    }

    public function __convertDate($str)
    {
      return new \DateTime($str);DateTime::createFromFormat(DateTime::ISO8601, $str);
    }

    public function __ProcessSeperateVideo($videoid)
    {
      $video_check = \App\Video::where('YT_ID', $videoid)->first();

      $update_video_info = false;
      if (count($video_check) != 0){
          $update_video_info = true;
      }

      // Get API info
      $raw = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,status&id=" . $videoid . "&key=" . env('YT_API_KEY'));

      $json = json_decode($raw);

      $vid_not_found = false;
      if(count($json->items) == 0 && !$update_video_info)
      {
        return "Video not found!";
      }

      if(count($json->items) == 0)
      {
        $vid_not_found = true;
      }

      // Video has been deleted, private or copyright takedown!
      if($update_video_info && $vid_not_found)
      {
        $vid_check = \App\Video::where('YT_ID', $videoid)->first();

        $vcl_status_new = new \App\VideoChangeLog;
        $vcl_status_new->Video_ID = $vid_check->id;
        $vcl_status_new->YT_Status = "not found";
        $vcl_status_new->save();

        $vid_check->YT_Status = "not found";
        $vid_check->save();

        return true;
      }

      $chan_check = \App\Channel::where('YT_ID', $json->items[0]->snippet->channelId)->get();

      if ($chan_check->isEmpty()) {
        // Chan not added. Add to DB
        $this->__ProcessNewChan($json->items[0]->snippet->channelId, true);
      }

      $chan_check = \App\Channel::where('YT_ID', $json->items[0]->snippet->channelId)->first();

      if(!$update_video_info)
      {
        $v = new \App\Video;
        $v->YT_ID = $json->items[0]->id;
        $v->Chan_ID = $chan_check->id;
        $v->Title = $json->items[0]->snippet->title;
        $v->Description = $json->items[0]->snippet->description;
        $v->YT_Status = $json->items[0]->status->privacyStatus;
        $v->Upload_Date = $this->__convertDate($json->items[0]->snippet->publishedAt);
        $v->save();
      }

      // Grab video instantly to get the ID in future calls
      $vid_check = \App\Video::where('YT_ID', $json->items[0]->id)->first();

      if(!$update_video_info)
      {
        // Add first change log
        $vcl = new \App\VideoChangeLog;
        $vcl->Video_ID = $vid_check->id;
        $vcl->Title = $json->items[0]->snippet->title;
        $vcl->Description = $json->items[0]->snippet->description;
        $vcl->YT_Status = $json->items[0]->status->privacyStatus;
        $vcl->save();
      }

      if($update_video_info)
      {
        // TITLE
        if($vid_check->Title != $json->items[0]->snippet->title)
        {
          $vcl_title_new = new \App\VideoChangeLog;
          $vcl_title_new->Video_ID = $vid_check->id;
          $vcl_title_new->Title = $json->items[0]->snippet->title;
          $vcl_title_new->save();

          $vid_check->Title = $json->items[0]->snippet->title;
        }

        // DESCRIPTION
        if($vid_check->Description != $json->items[0]->snippet->description)
        {
          $vcl_desc_new = new \App\VideoChangeLog;
          $vcl_desc_new->Video_ID = $vid_check->id;
          $vcl_desc_new->Description = $json->items[0]->snippet->description;
          $vcl_desc_new->save();

          $vid_check->Description = $json->items[0]->snippet->description;
        }

        // YT STATUS
        if($vid_check->YT_Status != $json->items[0]->status->privacyStatus)
        {
          $vcl_status_new = new \App\VideoChangeLog;
          $vcl_status_new->Video_ID = $vid_check->id;
          $vcl_status_new->YT_Status = $json->items[0]->status->privacyStatus;
          $vcl_status_new->save();

          $vid_check->YT_Status = $json->items[0]->status->privacyStatus;
        }

        $vid_check->save();
      }

      return true;
    }
}
