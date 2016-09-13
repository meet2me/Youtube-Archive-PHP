@extends("base")

@section("content")
<div class="row">
  <div class="col-md-2">
    @include('sidebar.left')
  </div>
  <div class="col-md-8">
    <ul class="list-group">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th></th>
            <th>Title</th>
            <th>Detail</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($channels as $chan)
            <tr>
              <td><img src="{{ $chan->ThumbnailURL }}" width=32 height=32></img></td>
              <td><a href="/chan/{{ $chan->YT_ID }}">{{ $chan->Title }}</a> <a href="https://www.youtube.com/channel/{{ $chan->YT_ID }}">[YT]</a></td>
              <td>
                <span class="tag tag-success" title="Downloads">{{ $chanstats[$chan->id]['dl'] }}</span>
                <span class="tag tag-info" title="Not Downloaded">{{ $chanstats[$chan->id]['nodl'] }}</span>
                |
                <span class="tag tag-success" title="Public">{{ $chanstats[$chan->id]['v_pub'] }}</span>
                <span class="tag tag-warning" title="Unlisted">{{ $chanstats[$chan->id]['v_unlisted'] }}</span>
                <span class="tag tag-danger" title="Not Found">{{ $chanstats[$chan->id]['v_notfound'] }}</span>
              </td>
              <td></td>
            </tr>
          @empty
          <tr>
            <td colspan="6" class="centerbold">No Videos!</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </ul>
  </div>
  <div class="col-md-2">
    <form class="boxdotted" action="/add" method="post">
      <div class="form-group">
        <label for="fgChanID">Add new Channel</label>
        <input type="text" class="form-control" id="fgChanID" name="chanID" placeholder="Channel ID">
      </div>

      <!-- Buttons have a value of 1 so the code can work out which button was pressed -->
      <button type="submit" name="submitChID" value="1" class="btn btn-primary">Add Channel ID</button>
      <button type="submit" name="submitChName" value="1" class="btn btn-primary">Add Channel Name</button>
    </form>

    <form class="boxdotted" action="/addvideo" method="post">
      <div class="form-group">
        <label for="fgVideoID">Add individual video</label>
        <input type="text" class="form-control" id="fgVideoID" name="videoID" placeholder="Video ID">
      </div>

      <button type="submit" name="submitChID" class="btn btn-primary">Add Video</button>
    </form>
  </div>
</div>
@endsection
