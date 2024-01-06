@if($comments !== null && count($comments) > 0)
    @foreach($comments as $comment)
        @php 
                    
            $className = 'alert-info';
            $score = $comment['score'];
            if($score > 5 && $score < 10) $className = 'alert-primary';
            if($score < 2) $className = 'alert-secondary';
            if($score > 10 && $score < 15) $className = 'alert-warning';
            if($score > 15) $className = 'alert-primary';

        @endphp

        <div class="alert {{$className}} alert-dismissible fade show showComment" data-commentId="{{$comment['id']}}" role="alert">
            <h4 class="alert-heading">{{$comment['user_username']}} <span class="badge bg-white text-dark" style="float:right">{{$comment['user_score']}}</span></h4>
            <hr>
            <p>{!! $comment['body'] !!}</p>
            <hr>
            <p class="mb-0"><span class="badge bg-white text-dark"><b>{{$comment['score']}}</b>++</span>
        </div>
    @endforeach    
@endif