<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function changeLocale(Request $request)
    {
        $request->validate(['locale' => 'required|in:en,am,om,ti']);
        session(['locale' => $request->locale]);
        return redirect()->back();
    }
}
