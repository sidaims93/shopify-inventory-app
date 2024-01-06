@extends('devRantLayouts.app')
@section('content')
<div class="col-md-10 offset-md-1">
    <div class="alert alert-primary alert-dismissible" role="alert">
        <h4 class="alert-heading">
            {{$profileDetails['profile']['username']}} 
            <span class="badge bg-white text-dark" style="float:right">{{number_format($profileDetails['profile']['score'])}}</span>
        </h4>
        <hr>
        <table class="table table-responsive">
            <tbody>
                <tr>
                    <td>About</td>
                    <td>{{$profileDetails['profile']['about']}}</td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>{{$profileDetails['profile']['location']}}</td>
                </tr>
                <tr>
                    <td>Skills</td>
                    <td>{{$profileDetails['profile']['skills']}}</td>
                </tr>
                <tr>
                    <td>Website</td>
                    <td><a href="#" data-url="//{!! $profileDetails['profile']['website'] !!}" class="btn btn-link website">Click here</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.website').click(function (e) {
                e.preventDefault();
                var el = $(this);
                var url = el.data('url');
                window.open(url, '_blank').focus();
            }); 
        });
    </script>
@endsection