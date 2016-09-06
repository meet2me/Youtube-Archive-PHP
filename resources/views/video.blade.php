@extends("base")

@section("content")
<div class="row">
  <div class="col-md-2">
    @include('extras.diskspace')
  </div>
  <div class="col-md-8">
    <ul class="list-group">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Channel</th>
            <th>Video ID</th>
            <th>Status</th>
            <th>Upload Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ $video["Title"] }}</td>
            <td><img src="{{ $chan->ThumbnailURL }}" width=32 height=32></img> <a href="/chan/{{ $chan->YT_ID }}">{{ $chan->Title }}</td>
            <td><a href="https://www.youtube.com/watch?v={{ $video["YT_ID"] }}">{{ $video["YT_ID"] }}</a></td>
            @if ($video["YT_Status"] == 'not found')
              <td class="table-danger">
            @endif
            @if ($video["YT_Status"] == 'unlisted')
              <td class="table-warning">
            @endif
            @if ($video["YT_Status"] == 'public')
              <td class="table-success">
            @endif
              {{ $video["YT_Status"] }}
            </td>
            <td>{{ $video["Upload_Date"] }}</td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </ul>

    <div class="boxdotted">
      <video width="1280" height="720" preload="metadata" controls>
        <source src="/videos/{{ $chan["YT_ID"] }}/{{ basename($video["File_Name"]) }}" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>
  </div>
  <div class="col-md-2"></div>
</div>
@endsection
