@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mb-5">
                <div class="row my-3">
                    <div class="col">
                        <div class="text-center">
                            <a class="btn btn-success btn-lg" href="{{ route('create_company') }}" title="Create preset">Create Company</a>
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
                    <h5 class="card-header">Companies</h5>
                    <div class="card-body">
                        @foreach ($companies as $company)
                            <ul>
                                <li>
                                    <a href="{{ route('edit_company', ['company' => $company]) }}"
                                       title="{{ $company->name }}">{{ $company->name }}</a>
                                </li>
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection