@extends('devRantLayouts.app')
@section('content')
<div class="col-md-10 offset-md-1">
    @php 
        $className = 'alert-info';
        $score = $rant['score'];
        if($score > 5 && $score < 10) $className = 'alert-primary';
        if($score < 2) $className = 'alert-secondary';
        if($score > 10 && $score < 15) $className = 'alert-warning';
        if($score > 15) $className = 'alert-primary';
    @endphp
            
    <div class="alert {{$className}} alert-dismissible fade show showRant" data-rantId="{{$rant['id']}}" style="cursor: pointer;"  role="alert">
        <h4 class="alert-heading">{{$rant['user_username']}} <span class="badge bg-white text-dark" style="float:right">{{$rant['user_score']}}</span></h4>
        <hr>
        <p>{!! $rant['text'] !!}</p>
        <hr>
        @include('devRant.rantStats')
    </div>

    <div class="col-md-8 offset-md-2">
        <h4 class="mb-2 mt-2">Comments</h4>
        @include('devRant.comments', ['comments' => $comments])
    </div>
</div>
@endsection