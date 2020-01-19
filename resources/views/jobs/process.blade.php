@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="text-center">Process Jobs</h1>
                <hr/><br/>
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="row">
                    <div class="col">
                        <div id="app">
                            <process-job
                                :jobs='@json($jobs)'
                                :presets='@json($presets)'
                                :selected-jobs='@json($selectedJobs)'
                                :meta-info='@json($metaInfo)'
                            ></process-job>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection