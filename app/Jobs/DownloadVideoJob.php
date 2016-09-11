<?php

namespace App\Jobs;

use \App\Video;

class DownloadVideoJob extends Job
{
    protected $chan_yt_id;
    protected $db_video_id;

    public function __construct($chan_yt_id, $db_video_id)
    {
      $this->chan_yt_id = $chan_yt_id;
      $this->db_video_id = $db_video_id;
    }

    public function handle()
    {
      \Log::info('[Download] Starting: C:' . $this->chan_yt_id . ' V:' . $this->db_video_id);
      $vid_db = \App\Video::where('id', $this->db_video_id)->first();

      $dlpath = env('DL_LOC') . '/' . $this->chan_yt_id;
      if(!file_exists($dlpath))
      {
        mkdir($dlpath);
      }

      exec('youtube-dl --write-sub --all-subs --write-description --write-info-json --write-annotations --write-thumbnail -f bestvideo[ext!=webm]+bestaudio[ext!=webm]/best[ext!=webm] -w -o "' . $dlpath . @"/%(id)s.%(ext)s" . '" https://www.youtube.com/watch?v=' . $vid_db->YT_ID);

      $this->__processDownload($dlpath, $vid_db->YT_ID);

      \Log::info('[Download] Finished: C:' . $this->chan_yt_id . ' V:' . $this->db_video_id);
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
}
