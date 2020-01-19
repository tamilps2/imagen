@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row my-3">
                    <div class="col">
                        <div class="text-center">
                            <a class="btn btn-success btn-lg" href="{{ route('create_job') }}" title="Create job">Create
                                a new Job</a>
                        </div>
                    </div>
                </div>
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="card">
                    <h5 class="card-header">
                        Jobs @if(count($jobs) === 0) <span class="text-muted">(No jobs uploaded)</span> @endif
                    </h5>
                    <div class="card-body">
                        <div>
                            <ul>
                                @foreach ($jobs as $job)
                                    <li>
                                        <a href="{{ route('view_job', ['job' => $job]) }}" title="JOb {{ $job->id }}">
                                            {{ $job->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
