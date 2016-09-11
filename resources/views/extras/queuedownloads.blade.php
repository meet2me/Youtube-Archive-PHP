<div class="boxdotted">
  <p class="side-title">Videos Queued</p>
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <th>Title</th>
        <th>Chan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($queue as $q)
        <tr>
          <td><a href="https://www.youtube.com/watch?v={{ $q['Video_YT_ID'] }}">{{ $q['Video_Title'] }}</a></td>
          <td><a href="/chan/{{ $q['Chan_YT_ID'] }}">{{ $q['Chan_Title'] }}</a></td>
        </tr>
        <tr>
          <td colspan="2">Date Created: {{ $q['Date_Created'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="2" class="centerbold">No videos to download!</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
