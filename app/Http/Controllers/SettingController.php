<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use App\Http\Requests\UpdateSettingRequest;

use Illuminate\Http\Request;

class SettingController extends Controller
{

	public function index()
	{
		return view('setting');
	}

	public function update(UpdateSettingRequest $request, SettingService $setting)
	{
		$setting->store($request->all(), $request->logo);

		return back()->with('success', 'Sukses Mengedit Pengaturan');
	}
}
