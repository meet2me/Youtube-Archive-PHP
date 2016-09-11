<?php

namespace App\Http\Controllers;

use DB;

use \App\Video;

class VideoController extends Controller
{
  public function index($id)
  {
    $video = \App\Video::where('YT_ID', $id)
                ->first();

    if(count($video) == 0)
    {
      return "Video not found!";
    }

    $chan = \App\Channel::where('id', $video->Chan_ID)
                ->first();

    return view('video', [
      'video' => $video,
      'chan' => $chan,
      'disk' => $this->__getDiskInfo(),
      'queued_ids' => $this->__getQueuedDownloadsIDs(),
    ]);
  }
}
