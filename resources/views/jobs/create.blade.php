@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-center">Create new Jobs</h1>
        <hr/>
        <br/>
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col">
                <div id="app">
                    <file-uploader></file-uploader>
                </div>
            </div>
        </div>
    </div>
@endsection