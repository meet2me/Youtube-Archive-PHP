@extends("base")

@section('content')
<div class="row">
  <div class="col-md-2">
    @include('sidebar.left')
  </div>
  <div class="col-md-8">
    <h1>{{ $chan->Title }}</h1>

    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>Title</th>
          <th>Video ID</th>
          <th>Youtube Status</th>
          <th>Upload Date</th>
          <th>File Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($videos as $vid)
          @if ($vid["YT_Status"] == 'not found')
            <tr class="table-danger">
          @endif
          @if ($vid["YT_Status"] == 'unlisted')
            <tr class="table-warning">
          @endif
          @if ($vid["YT_Status"] == 'public')
            <tr>
          @endif
            <td>{{ $vid["Title"] }}</td>
            <td><a href="https://www.youtube.com/watch?v={{ $vid["YT_ID"] }}">{{ $vid["YT_ID"] }}</a></td>
            <td>
              @if ($vid["YT_Status"] == 'not found')
                <span class="tag tag-danger" title="Please click on the left link to check why the video is not available">NOT FOUND!</span>
              @elseif ($vid["YT_Status"] == 'public')
                <span class="tag tag-success">PUBLIC</span>
              @elseif ($vid["YT_Status"] == 'unlisted')
                <span class="tag tag-warning">Unlisted!</span>
              @else
                {{ $vid["YT_Status"] }}
              @endif
            </td>
            <td>{{ $vid["Upload_Date"] }}</td>
            <td>{{ $vid["File_Status"] }}</td>
            <td>
              @if ($vid["File_Name"] == '')
                @if (isset($queued_ids[$vid["id"]]))
                  <img src="/assets/img/icons/drive_go.png" title="In Queue to Download">
                @else
                  <a href="/chan/{{ $id }}/download/{{ $vid["id"] }}"><img src="/assets/img/icons/add.png" title="Download Video"></a>
                @endif
              @else
                  <a href="/videos/{{ $id }}/{{ basename($vid["File_Name"]) }}"><img src="/assets/img/icons/disk.png" title="Watch"></a>
                  <a href="/video/{{ $vid["YT_ID"] }}"><img src="/assets/img/icons/information.png" title="Info"></a>
              @endif
              <a href="/chan/{{ $id }}/update/{{ $vid["YT_ID"] }}"><img src="/assets/img/icons/arrow_refresh.png" title="Update Info"></a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="centerbold">No Videos!</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="col-md-2">
    <div class="btn-group-vertical">
      <a href="/chan/{{ $id }}/update/uploads" class="btn btn-primary" role="button">Update Uploads & Get New Uploads</a>
      <a href="/chan/{{ $id }}/update/videos" class="btn btn-info" role="button">Update Listed Videos</a>
    </div>
  </div>
</div>
@endsection
