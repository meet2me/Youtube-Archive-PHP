<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Channel;
use \App\Video;

class HomeController extends Controller
{
    public function index()
    {
      $channels = \App\Channel::all();

      return view('home', ['channels' => $channels]);
    }

    public function addChan(Request $request)
    {
      $chanid = $request->input('chanID');

      if($request->has('submitChID'))
      {
        $result = $this->__ProcessNewChan($chanid, true);

        if($result != true)
        {
          return $result;
        }

        return redirect()->route('home');
      }

      if($request->has('submitChName'))
      {
        $result = $this->__ProcessNewChan($chanid, false);

        if($result != true)
        {
          return $result;
        }

        return redirect()->route('home');
      }

      return "Please use a submit button to add a channel";
    }

    public function addVideo(Request $request)
    {
      $videoid = $request->input('videoID');

      $result = $this->__ProcessSeperateVideo($videoid);

      if($result != true)
      {
        return $result;
      }

      return redirect()->route('home');
    }

    // Private methods
    public function __ProcessNewChan($chanID, $isid)
    {
      if($isid)
      {
          $raw = file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=snippet,contentDetails&id=" . $chanID . "&key=" . env('YT_API_KEY'));
      }else
      {
          $raw = file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=snippet,contentDetails&forUsername=" . $chanID . "&key=" . env('YT_API_KEY'));
      }

      $json = json_decode($raw);

      if(count($json->items) == 0)
      {
        return "No Channels found!";
      }

      $chan_check = \App\Channel::where('YT_ID', $json->items[0]->id)->get();

      if (!$chan_check->isEmpty()){
          return "Channel already exists";
      }

      // Chan not added. Add to DB

      $c = new \App\Channel;

      $c->YT_ID = $json->items[0]->id;
      $c->Title = $json->items[0]->snippet->title;
      $c->Description = $json->items[0]->snippet->description;
      $c->ThumbnailURL = $json->items[0]->snippet->thumbnails->default->url;
      $c->UploadPlaylistID = $json->items[0]->contentDetails->relatedPlaylists->uploads;

      $c->save();

      return true;
    }

    public function __ProcessSeperateVideo($videoid)
    {
      $video_check = \App\Video::where('YT_ID', $videoid)->get();

      $update_video_info = false;
      if (!$video_check->isEmpty()){
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

    public function __convertDate($str)
    {
      return new \DateTime($str);DateTime::createFromFormat(DateTime::ISO8601, $str);
    }
}
