<div class="addnew">
  <p>Disk Space: {{ $disk['format_free'] }} / {{ $disk['format_total'] }}</p>
  <progress class="progress progress-info" value="{{ $disk['free'] }}" max="{{ $disk['total'] }}"></progress>
</div>
