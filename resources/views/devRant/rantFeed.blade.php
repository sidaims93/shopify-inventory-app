@extends('devRantLayouts.app')
@section('content')
@if(Auth::check())
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="form-control text-center">
            <a class="btn btn-lg btn-danger" href="#" id="createRant">Create a Rant</a>
        </div>
    </div>
</div>
@endif
<div class="col-md-6 offset-md-3">
    @if(isset($rants['body']['rants']))
        @foreach($rants['body']['rants'] as $rant)
            @php 
            
            $className = 'alert-info';
            $score = $rant['score'];
            if($score > 5 && $score < 10) $className = 'alert-primary';
            if($score < 2) $className = 'alert-secondary';
            if($score > 10 && $score < 15) $className = 'alert-warning';
            if($score > 15) $className = 'alert-primary';

            @endphp
        
            <div class="alert {{$className}} alert-dismissible" role="alert">
                <h4 class="alert-heading">
                    <a target="_blank" href="{{route('devRant.show.custom.profile', ['id' => $rant['user_id']])}}">
                        {{$rant['user_username']}} 
                    </a>
                    <span class="badge bg-white text-dark" style="float:right">{{number_format($rant['user_score'])}}</span>
                </h4>
                <hr>
                <p style="cursor: pointer;" class="showRant" data-rantId="{{$rant['id']}}" >{!! htmlSpecialChars($rant['text']) !!}</p>
                <hr>
                @include('devRant.rantStats')
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('modals')
<div class="modal fade singleRantModal" id="singleRantModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title rantHeader"></h5>
                <button type="button" style="float:right" id="singleRanter" class="btn-primary showRanterProfile">by <b>MammaNeedHummus</b></button>
            </div>
            <div class="modal-body rantContent" style="min-height: 750px;">
                <input type="hidden" id="getRantId">
            </div>
            <div class="modal-footer">
                <input type="text" class="form-control" placeholder="Enter your comment here...." />
                <button type="button" class="btn btn-primary mb-4">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- End Scrolling Modal-->
<!-- Start create rant Modal-->
<div class="modal fade createRantModal" id="createRantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title rantHeader">Post a rant</h5>
            </div>
            <div class="modal-body" style="min-height: 200px;">
                <textarea name="" class="form-control" id="rantContent" cols="30" rows="20"></textarea>
                <div class="row mt-4" id="rantError" style="display: none;">
                    <span class="badge bg-danger" id="rantErrorMsg"></span>
                </div>
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-primary mb-4 createRant">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- End Create Rant Modal-->
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('.showRant').click(function (e) {
            e.preventDefault();
            showSingleRant($(this));
        })

        $('#createRant').click(function (e) {
            e.preventDefault();
            showCreateRantModal();
        })

        $('.createRant').click(function (e) {
            e.preventDefault();
            var rantContent = $('#rantContent').val();
            $('#rantError').hide();
            $.ajax({
                type: 'POST',
                url: "{{route('devRant.postRant')}}",
                data: {
                    text: rantContent
                },
                async: false,
                success: function (response) {
                    if(response.status) {
                        window.top.location.reload(true);
                    } else {
                        showErrorMessage(response);
                    }
                }
            });
        }); 
    });

    function showSingleRant(el) {
        var rantId = el.attr('data-rantId');
        window.location.href="{{route('devRant.showRant')}}?rantId="+rantId;
    }

    function showCreateRantModal() {
        $('#createRantModal').modal('show');
    }

    function showErrorMessage(response) {
        $('#rantError').show();
        $('#rantErrorMsg').text(response.message);
    }
</script>
@endsection