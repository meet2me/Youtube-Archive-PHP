# Youtube Archiver

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Built Using

[Lumen](http://lumen.laravel.com/)



## Quick Guide

- Install respository 
- copy .env.example to .env
- Install dependencies: composer install
- Install [youtube-dl](https://youtube-dl.org/) library which will run shell command to save youtube video

## Evnironment Variables

- APP_ENV=local
- APP_DEBUG=true
- APP_KEY=base64:+k8dd/Tmh5nBMuslM5/mzdM5Ce98snd3CP+mvz3ldBk= (Generate it using php artisan key:generate)
- DB_CONNECTION=sqlite
- DB_DATABASE="/Users/techmates/Sites/Youtube-Archive-PHP/db.sqlite" (Relative path to the database)
- YT_API_KEY=YOUR_YT_API_KEY
- DL_LOC="/Users/techmates/Sites/Youtube-Archive-PHP/public/videos" (Relative path to the video storage directory; make sure that in 'public' directory you have new folder named 'videos' created)




## License

The Lumen framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

## Preview

![](https://i.imgur.com/Xb1SY9U.png)
