<div class="boxdotted">
  <p class="side-title">Disk Space</p>
  <p>{{ $disk['format_free'] }} / {{ $disk['format_total'] }}</p>
  <progress class="progress progress-info" value="{{ $disk['free'] }}" max="{{ $disk['total'] }}"></progress>
</div>
