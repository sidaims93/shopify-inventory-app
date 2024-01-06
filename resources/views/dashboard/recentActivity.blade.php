@foreach($logs as $log)
<div class="activity-item d-flex">
<div class="activite-label">{{Carbon\Carbon::parse($log['timestamp'])->diffForHumans()}}</div>
<i class="bi bi-circle-fill activity-badge text-{{$log['class']}} align-self-start"></i>
<div class="activity-content">
    {{$log['activity']}}
</div>
</div><!-- End activity item-->
@endforeach