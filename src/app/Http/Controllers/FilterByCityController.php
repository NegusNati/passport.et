<?php

namespace App\Http\Controllers;

use App\Models\PDFToSQLite;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FilterByCityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Passport/ByLocaion/ChooseLocation',        [
            'cities' => PDFToSQLite::distinct()->pluck('location'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function filterByLocation(Request $request)
    {
        $location = $request->input('location');
        $query = PDFToSQLite::query();

        if ($location) {
            $query->where('location', 'LIKE', '%' . $location . '%');
        }

        $passports = $query->orderBy('id', 'desc')->simplePaginate(50);

        return Inertia::render('Passport/TableView', [
            'passports' => $passports,
            'filter' => ['location' => $location],
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
