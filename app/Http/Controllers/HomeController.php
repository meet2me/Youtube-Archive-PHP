<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Channel;
use \App\Video;

class HomeController extends Controller
{
    public function index()
    {
      $channels = \App\Channel::orderByRaw('Title COLLATE NOCASE ASC')
                    ->get();

      $chanstats = null;
      foreach ($channels as $chan) {
        $chanstats[$chan->id] = $this->__getChanStats($chan->id);
      }

      return view('home', [
        'channels' => $channels,
        'chanstats' => $chanstats,
        'disk' => $this->__getDiskInfo(),
      ]);
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

    public function __getChanStats($id)
    {
      $stats = \App\Channel::where('id', $id)->first();

      return array(
        'dl' => $stats->stats_dl,
        'nodl' => $stats->stats_nodl,
        'v_pub' => $stats->stats_v_pub,
        'v_unlisted' => $stats->stats_v_unlisted,
        'v_notfound' => $stats->stats_v_notfound
      );

    }
}
