<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Requests\CompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{

    public function index()
    {
        $companies = Company::all();

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(CompanyRequest $request)
    {
        $logosFolder = config('imager.logos_dir', 'logos');
        $path = $request->file('logo')->store($logosFolder);

        $company = Company::create(array_merge(
            $request->validated(),
            ['logo' => pathinfo($path, PATHINFO_BASENAME)]
        ));

        session()->flash('status', 'Company created successfully');

        return redirect(route('companies'));
    }

    public function show(Company $company)
    {
        //
    }

    public function edit(Request $request, Company $company)
    {
        $hasClientCredentials = false;
        $clientId = config('services.google.client_id', '');
        $clientSecret = config('services.google.client_secret', '');

        if (!empty($clientId) && !empty($clientSecret)) {
            $hasClientCredentials = true;
        }

        $logosFolder = config('imager.logos_dir', 'logos');
        $logoPath = $logosFolder . DIRECTORY_SEPARATOR . $company->logo;

        return view('companies.edit', compact('company', 'logoPath', 'hasClientCredentials'));
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $company->update($request->validated());

        $logosFolder = config('imager.logos_dir', 'logos');
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store($logosFolder);
            $company->logo = pathinfo($logoPath, PATHINFO_BASENAME);
            $company->save();
        }

        session()->flash('status', 'Company details updated.');

        return redirect(route('edit_company', ['company' => $company]));
    }

    public function authorizeGoogle(Request $request)
    {
        if (!$request->has('state')) {
            session()->flash('status', 'There was no state set in the redirect auth url. Invalid auth redirect.');
            return redirect(route('companies'));
        }

        $state = $request->get('state');
        $params = explode('=', $state);
        $company = Company::whereId($params[1])->first();

        if (empty($company)) {
            session()->flash('status', 'No company found with the authorization state.');
            return redirect(route('companies'));
        }

        $accessToken = $company
            ->getGoogleClient()
            ->fetchAccessTokenWithAuthCode($request->get('code'));

        if ($request->has('error')) {
            session()->flash('status', 'Google Auth Error: ' . $request->get('error'));
        } else {
            session()->flash('status', 'Access token has been generated.');
            $company->google_access_token = $accessToken;
            $company->save();
        }

        return redirect(route('edit_company', ['company' => $company]));
    }

    public function revokeGoogle(Request $request, Company $company)
    {
        $company->getGoogleClient()->revokeToken();
        $company->google_access_token = '';
        $company->save();

        session()->flash('status', 'Access has been revoked successfully.');

        return redirect(route('edit_company', ['company' => $company]));
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect(route('companies'));
    }
}
