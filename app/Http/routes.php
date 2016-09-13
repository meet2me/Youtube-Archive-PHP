<?php

// Home
$app->get('/', [
    'as' => 'home', 'uses' => 'HomeController@index'
]);

$app->post('/add', [
    'as' => 'homeaddchan', 'uses' => 'HomeController@addChan'
]);

$app->post('/addvideo', [
    'as' => 'homeaddvideo', 'uses' => 'HomeController@addVideo'
]);

// Chan
$app->get('/chan/{id}', [
    'as' => 'chan', 'uses' => 'ChanController@index'
]);

$app->get('/chan/{id}/update/uploads', [
    'as' => 'chanupdateplaylist', 'uses' => 'ChanController@updatePlaylist'
]);

$app->get('/chan/{chid}/update/videos', [
    'as' => 'chanupdatevideos', 'uses' => 'ChanController@updateVideos'
]);

$app->get('/chan/{chid}/update/{vid}', [
    'as' => 'chanvidupdate', 'uses' => 'ChanController@updateVideo'
]);

$app->get('/chan/{id}/download/{vid}/silent', [
    'as' => 'chandownloadvideosilent', 'uses' => 'ChanController@downloadVideoSilent'
]);

$app->get('/chan/{id}/download/{vid}', [
    'as' => 'chandownloadvideo', 'uses' => 'ChanController@downloadVideo'
]);

// Video Info
$app->get('/video/{vid}', [
    'as' => 'videoinfo', 'uses' => 'VideoController@index'
]);
