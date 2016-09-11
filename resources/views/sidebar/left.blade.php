@include('extras.diskspace')
<br />
@if(!empty($recent_downloads))
    @include('extras.recentdownloads')
@endif
<br />
@if(!empty($queue))
    @include('extras.queuedownloads')
@endif
