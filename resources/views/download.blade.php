@extends("base")

@section("content")
<div class="row">
  <div class="col-md-2"></div>
  <div class="col-md-8">
    <ul class="list-group">
      @forelse ($channels as $chan)
        <a href="/chan/{{ $chan->YT_ID }}">
          <li class="list-group-item">
            <!--<span class="tag tag-default tag-pill pull-xs-right" title="Total Videos">50</span>
            <span class="tag tag-danger tag-pill pull-xs-right" title="Removed">1</span>
            <span class="tag tag-success tag-pill pull-xs-right" title="Downloaded">1</span>-->
            {{ $chan->Title }}
          </li>
        </a>
      @empty
          <p>No channels</p>
      @endforelse
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
  </div>
</div>
@endsection
