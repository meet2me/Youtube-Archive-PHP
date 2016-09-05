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
}
