<div class="boxdotted">
  <p class="side-title">Recent Downloads</p>
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <th>Title</th>
        <th>Chan</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($recent_downloads as $rdl)
        <tr>
          <td><a href="/video/{{ $rdl['Video_YT_ID'] }}">{{ $rdl['Video_Title'] }}</a></td>
          <td><a href="/chan/{{ $rdl['Chan_YT_ID'] }}">{{ $rdl['Chan_Title'] }}</a></td>
        </tr>
        <tr>
          <td colspan="2">Date Downloaded: {{ $rdl['Date_Created'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
