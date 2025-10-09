<?php

namespace App\Http\Controllers;

use App\Actions\Passport\SearchPassportsAction;
use App\Domain\Passport\Data\PassportSearchParams;
use App\Models\PDFToSQLite;
use App\Support\CacheKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PassportSearchController extends Controller
{
    public function __construct(private SearchPassportsAction $searchPassports)
    {
    }

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

        $params = PassportSearchParams::fromRequest($request, 'web');
        $passports = $this->searchPassports->execute($params);

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
        $page = (int) $request->get('page', 1);

        $passports = Cache::tags(['passports', 'passports.all'])
            ->remember(CacheKeys::passportsAll($page), 60, function () {
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
