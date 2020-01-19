@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="text-center">Company Details</h1>
        <hr/>
        <br/>
        <div class="row justify-content-center">
            <div class="col-8">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <form method="post" action="{{ route('update_company', ['company' => $company]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <h5 class="card-header">Company</h5>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="company_name">Name</label>
                                <input type="text" name="name" class="form-control" id="company_name"
                                       value="{{ old('name', $company->name) }}">
                                @error('name')
                                <small class="text-danger">{{ $errors->first('name') }}</small>
                                @enderror
                            </div>
                            <div class="row my-3 text-center">
                                <div class="col">
                                    <img src="{{ asset($logoPath) }}" class="img-thumbnail" width="100"
                                         height="100"/>
                                </div>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="logo" class="custom-file-input" id="logo">
                                <label class="custom-file-label" for="customFile">Choose logo</label>
                                @error('logo')
                                <small class="text-danger">{{ $errors->first('logo') }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <br/>
                    <div class="card">
                        <div class="card-header">FTP Details</div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="ftp_host">FTP Host</label>
                                <input type="text" name="ftp_host" class="form-control" id="ftp_host"
                                       value="{{ old('ftp_host', $company->ftp_host) }}">
                                @error('ftp_host')
                                <small class="text-danger">{{ $errors->first('ftp_host') }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="ftp_username">FTP Username</label>
                                <input type="text" name="ftp_username" class="form-control" id="ftp_username"
                                       value="{{ old('ftp_username', $company->ftp_username) }}">
                                @error('ftp_username')
                                <small class="text-danger">{{ $errors->first('ftp_username') }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="ftp_password">FTP Password</label>
                                <input type="text" name="ftp_password" class="form-control" id="ftp_password"
                                       value="{{ old('ftp_password', $company->ftp_password) }}">
                                @error('ftp_password')
                                <small class="text-danger">{{ $errors->first('ftp_password') }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="ftp_password">FTP Upload Path</label>
                                <input type="text" name="ftp_upload_path" class="form-control" id="ftp_upload_path"
                                       value="{{ old('ftp_upload_path', $company->ftp_upload_path) }}">
                                <small class="form-text">If not provided, will default to the user home directory.</small>
                                @error('ftp_upload_path')
                                <small class="text-danger">{{ $errors->first('ftp_upload_path') }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <br/>
                    <div class="card">
                        <div class="card-header">Google Services - Youtube</div>
                        <div class="card-body">
                            <div class="row my-4 text-center">
                                <div class="col">
                                    @if ($hasClientCredentials && !$company->hasValidAccessToken())
                                        <a
                                            class="btn btn-primary"
                                            target="_blank"
                                            href="{{ $company->getGoogleClient()->createAuthUrl() }}"
                                        >Authorize Youtube API</a>
                                    @else
                                        <p class="alert alert-info"><b>Note:</b> Configure your application with the client details to authorize youtube.</p>
                                    @endif

                                    @if ($company->hasValidAccessToken())
                                        <a
                                            class="btn btn-danger"
                                            href="{{ route('revoke', ['company' => $company]) }}"
                                        >Revoke Youtube Access</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row my-3 text-center">
                        <div class="col">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row justify-content-center my-2">
            <div class="col-8">
                <form action="{{ route('destroy_company', ['company' => $company]) }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-block btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
@endsection