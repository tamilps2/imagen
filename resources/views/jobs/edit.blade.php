@extends('layouts.app')

@section('content')
    <div class="container">
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
                <form method="POST" action="{{ route('update_job', ['job' => $job]) }}">
                    @csrf
                    <div class="card">
                        <h5 class="card-header">Edit Job</h5>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">Job Title</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="{{ old('title', $job->title) }}">
                                @error('title')
                                <small class="form-text text-danger">{{ $errors->first('title') }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="directory_prefix">Job directory prefix</label>
                                <input type="text" class="form-control" id="directory_prefix" name="directory_prefix"
                                       value="{{ old('directory_prefix', $job->directory_prefix) }}">
                                @error('directory_prefix')
                                <small class="form-text text-danger">{{ $errors->first('directory_prefix') }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="preset_id">Choose a preset for the Job</label>
                                <select name="preset_id" class="form-control" id="preset_id">
                                    <option value="">Choose a preset</option>
                                    @foreach ($presets as $preset)
                                        @if ($preset->id == old('preset_id', $job->preset_id))
                                            <option value="{{ $preset->id }}" selected>{{ $preset->name }}</option>
                                        @else
                                            <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('preset_id')
                                <small class="form-text text-danger">{{ $errors->first('preset_id') }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection