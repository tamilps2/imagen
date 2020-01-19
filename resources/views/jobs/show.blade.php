@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h3 class="text-center border-bottom py-2">Job: {{ $job->title }}</h3>
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
        <div class="card my-3">
            <div class="card-header">
                Files
            </div>
            <ul class="list-group list-group-flush">
                @foreach ($job->files as $file)
                    <li class="list-group-item">
                        <blockquote class="blockquote">
                            <p class="mb-0">{{ $file->original_name }}</p>
                            <footer class="blockquote-footer">{{ $file->storage_path }}</footer>
                        </blockquote>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="row text-center pt-2 pb-2 mx-1 my-2 bg-dark rounded">
            <div class="col">
                <a
                    href="{{ $job->is_processing ? '#' : route('start_process', ['jobs' => $job->id]) }}"
                    class="btn btn-primary"
                    @if ($job->is_processed) onclick="return confirm('Job is already processed. Do you wish to process again?')" @endif
                    title="{{ $job->is_processing ? 'Job is currently processing' : 'Start job processing' }}"
                >
                    @if ($job->is_processing)
                        Processing...
                    @elseif ($job->is_processed)
                        Process Again
                    @else
                        Process
                    @endif
                </a>
                <br/>
                <strong class="text-info">
                    @if ($job->is_processing)
                        Job is currently being processed
                    @elseif ($job->is_processed)
                        Job is already processed, click to process again.
                    @else
                        Choose the preset to process this job with.
                    @endif
                </strong>
            </div>
            <div class="col">
                <form method="POST" action="{{ route('delete_job', ['job' => $job]) }}">
                    @csrf
                    <button
                        type="submit" class="btn btn-danger"
                        onclick="return confirm('Proceed to delete this job ?')"
                        {{ $job->is_processing ? 'disabled' : '' }}
                        title="{{ $job->is_processing ? 'Cannot delete while job is processing' : 'Delete job' }}"
                    >
                        Delete Job
                    </button>
                    <br/>
                    <strong class="text-danger">
                        @if ($job->is_processing)
                            Cannot delete while job is processing
                        @elseif ($job->is_processed)
                            This job is processed. Delete with caution!!!
                        @else
                            Deleting the job will delete all the files included in this job.
                        @endif
                    </strong>
                </form>
            </div>
        </div>
    </div>
@endsection