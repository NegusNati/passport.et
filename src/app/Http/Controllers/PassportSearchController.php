<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\PDFToSQLite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\get;

class PassportSearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {


        return Inertia::render('Passport/Index');

        // return Inertia::render('Profile/Edit', [
        //     'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
        //     'status' => session('status'),
        // ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Request $request)
    {

        $query = PDFToSQLite::query();
        $requestNumber = $request->input('requestNumber');
        $firstName = $request->input('firstName');
        $middleName = $request->input('middleName');
        $lastName = $request->input('lastName');

        $query->when($requestNumber, function ($q) use ($requestNumber) {
            return $q->where('requestNumber', 'LIKE', $requestNumber . '%');
        })
            ->when(!$requestNumber && ($firstName || $middleName || $lastName), function ($q) use ($firstName, $middleName, $lastName) {
                return $q->where(function ($subQ) use ($firstName, $middleName, $lastName) {
                    $subQ->when($firstName, function ($q) use ($firstName) {
                        return $q->where('firstName', 'LIKE', $firstName . '%');
                    })
                        ->when($middleName, function ($q) use ($middleName) {
                            return $q->where('middleName', 'LIKE', $middleName . '%');
                        })
                        ->when($lastName, function ($q) use ($lastName) {
                            return $q->where('lastName', 'LIKE', $lastName . '%');
                        });
                });
            });

            

        $passports = $query->limit(60)->get();


        return Inertia::render(
            'Passport/Show',
            [
                'passports' => $passports,
                'search' => $request->all(),

            ]
        );
    }
    public function detail(Request $request, $id)
    {
        $passport = PDFToSQLite::findOrFail($id);

        return Inertia::render('Passport/ShowDetail', [
            'passport' => $passport,

        ]);
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
    public function all(Request $request)
    {

        // $passports = PDFToSQLite::latest()->simplePaginate(50)->fragment("fragment-id");
        $passports = Cache::remember('all_passports_page_' . $request->get('page', 1), 60, function () {
            return PDFToSQLite::query()->orderBy('id', 'desc')->simplePaginate(50);
        });
        $passports->setPath(url('/all-passports'));


        return Inertia::render('Passport/TableView', [
            'passports' => $passports,

        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {}
}
