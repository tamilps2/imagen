@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row my-3">
                    <div class="col">
                        <div class="text-center">
                            <a class="btn btn-success btn-lg" href="{{ route('create_job') }}" title="Create job">Create a new Job</a>
                        </div>
                    </div>
                </div>
                @if (session('status'))
                    <div class="alert alert-success my-3" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <div class="card">
                    <h5 class="card-header">Jobs @empty($jobs) <span>(No jobs uploaded)</span> @endempty</h5>
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
