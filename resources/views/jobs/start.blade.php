@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('view_job', ['job' => $job]) }}">Job</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Process</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <form method="POST" action="{{ route('process_job') }}">
                    <div id="app">
                        <file-uploader></file-uploader>
                    </div>

                    <div class="row my-3">
                        <div class="col">
                            <button type="submit" class="btn btn-primary">Process</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection