@extends('layouts.app')

@section('content')
    <div class="container">
        <div id="app">
            <preset-form
                    :preset="{{ @json_encode($preset) }}"
            ></preset-form>
            <div class="row justify-content-center">
                <div class="col-8">
                    <form method="post" action="{{ route('remove_preset', ['preset' => $preset]) }}">
                        @csrf
                        <button onclick="return confirm('Are you sure to delete the preset?')" type="submit"
                                class="btn btn-block btn-danger">Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
