<?php

namespace App\Http\Controllers;

use App\DataTables\AncestorDataDataTable;
use App\Models\AncestorData;
use App\Models\AncestorLocalTravelDetail;
use App\Http\Requests\StoreAncestorDataRequest;
use App\Http\Requests\UpdateAncestorDataRequest;
use App\Models\AncestorNote;
use App\Models\AncestorInternationalTravelDetail;
use App\Models\AncestorSpouse;
use App\Models\Countries;
use App\Models\Gender;
use App\Models\Occupation;
use App\Models\Ship;
use App\Models\SourceOfArrival;
use App\Models\States;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AncestorDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AncestorDataDataTable $request)
    {
        $user = auth()->user();

        $memberId = $user->member_id;

        $state = States::orderBy('id', 'asc')->pluck('name')->toArray();
        array_unshift($state, '');

        $occupation = Occupation::orderBy('name', 'asc')->pluck('name')->toArray();
        array_unshift($occupation, '');

        $gender_name = Gender::orderBy('name', 'asc')->pluck('name')->toArray();
        array_unshift($gender_name, '');

        $country = Countries::orderBy('name', 'asc')->pluck('name')->toArray();
        array_unshift($country, '');

        $ship = Ship::orderBy('name_of_ship', 'asc')->pluck('name_of_ship')->toArray();
        array_unshift($ship, '');

        $source_of_arrivals = SourceOfArrival::orderBy('name', 'asc')->pluck('name')->toArray();
        array_unshift($source_of_arrivals, '');

        // If the user has role_id = 1 (e.g., admin), show all ancestors
        if ($user->role_id == 1) {
            $ancestor = AncestorData::with([
                'occupation_relation',
                'Gender',
                'Ships',
                'departureCountry',
                'state',
                'sourceOfArrival',
                'spouse_details',
                'mode_of_travel.ship',
                'notes'
            ])->get();
        } else {
            $ancestorIds = DB::table('member_ancestor')
                ->where('member_id', $memberId)
                ->pluck('ancestor_id')
                ->toArray();

            $ancestor = AncestorData::with([
                'occupation_relation',
                'Gender',
                'Ships',
                'departureCountry',
                'state',
                'sourceOfArrival',
                'spouse_details',
                'mode_of_travel.ship',
                'notes'
            ])->whereIn('id', $ancestorIds)
                ->get();
        }

        return $request->render('page.ancestor-data.index', compact('ancestor', 'state', 'occupation', 'gender_name', 'country', 'ship', 'source_of_arrivals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $defaultState = States::where("name", "South Australia")->first();
        return view('page.ancestor-data.create', compact('defaultState'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAncestorDataRequest  $request
     * @return \Illuminate\Http\Response
     */

    public function store(StoreAncestorDataRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            $ancestorData = AncestorData::create($validatedData);

            if ($ancestorData->source_of_arrival == 1 || $ancestorData->source_of_arrival == 2) {
                $ancestorData->mode_of_travel_id = $request->input('mode_of_travel_id');
                $ancestorData->save();
            } else {
                $this->saveTravelDetails($ancestorData, $validatedData, $request);
            }

            // Save spouse details
            $this->saveAncestorSpouse($ancestorData, $request);

            // Save ancestor notes, birth details, and death details
            $this->saveAncestorNotes($ancestorData->id, $validatedData);


            $filteredData = array_filter($validatedData, function ($value) {
                return !is_null($value);
            });

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Ancestor Details Saved Successfully",
                "redirectTo" => url("ancestor-data/{$ancestorData->id}")
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());

            return response()->json([
                "status" => false,
                "message" => "An error occurred while creating AncestorData. Please try again later.",
            ], 500);
        }
    }

    private function formatDate(&$data, $field)
    {
        if (isset($data[$field])) {
            $data[$field] = Carbon::createFromFormat('Y-m-d', $data[$field])->format('Y-m-d');
        }
    }
    private function saveAncestorNotes($ancestorId, $validatedData)
    {
        AncestorNote::create([
            'ancestor_id' => $ancestorId,
            'notes' => $validatedData['notes'] ?? null,
            'birth_details' => $validatedData['birth_details'] ?? null,
            'death_details' => $validatedData['death_details'] ?? null,
        ]);
    }
    private function saveAncestorSpouse($ancestorData, $request)
    {
        try {
            //Log::info('Saving ancestor spouse data', $request->only(['marriage_date', 'marriage_place', 'spouse_place_of_birth', 'spouse_place_of_death', 'spouse_family_name', 'spouse_given_name', 'spouse_birth_date', 'spouse_death_date']));

            $ancestorSpouse = new AncestorSpouse();
            $ancestorSpouse->ancestor_id = $ancestorData->id;
            $ancestorSpouse->fill($request->only(['marriage_date', 'marriage_month', 'marriage_year', 'marriage_place', 'spouse_birth_place', 'spouse_death_place', 'spouse_family_name', 'spouse_given_name', 'spouse_birth_date', 'spouse_birth_month', 'spouse_birth_year', 'spouse_death_date', 'spouse_death_month', 'spouse_death_year']));
            $ancestorSpouse->save();

            //Log::info('Ancestor spouse data saved successfully', ['ancestor_spouse_id' => $ancestorSpouse->id]);
        } catch (\Exception $e) {
            //Log::error('Error saving ancestor spouse data: ' . $e->getMessage());
            throw $e; // Rethrow the exception to trigger a rollback
        }
    }

    private function saveTravelDetails($ancestorData, $validatedData, $request)
    {
        $travelDetailData = new AncestorLocalTravelDetail();
        $travelDetailData->ancestor_id = $ancestorData->id;

        if (isset($validatedData['arrival_date_in_sa'])) {
            $travelDetailData->travel_date = Carbon::createFromFormat('Y-m-d', $validatedData['arrival_date_in_sa'])->format('Y-m-d');
        }

        if (isset($validatedData['evidence_of_arrival'])) {
            $travelDetailData->description = $validatedData['evidence_of_arrival'];
        }

        $travelDetailData->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AncestorData  $ancestorData
     * @return \Illuminate\Http\Response
     */
    public function show($ancestorData)
    {
        $user = auth()->user();

        if ($user->role_id != 1) {
            // Check if the ancestor belongs to the current user by querying the member_ancestor table
            $isRelated = DB::table('member_ancestor')
                ->where('member_id', $user->member_id)
                ->where('ancestor_id', $ancestorData)
                ->exists();
    
            if (!$isRelated) {
                // Redirect the user to their own member page with an error message
                return back()->with('error', 'You are not authorized to view this member\'s details.');
            }
        }

        $ancestor = AncestorData::with(
            [
                'gender',
                'mode_of_travel',
                'occupation_relation',
                'localTravelDetails',
                'spouse_details',
                'notes'
            ]
        )->find($ancestorData);

        return view('page.ancestor-data.view', compact('ancestor'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AncestorData  $ancestorData
     * @return \Illuminate\Http\Response
     */
    public function edit($ancestorData)
    {
        $ancestor = AncestorData::with(
            [
                'gender',
                'mode_of_travel',
                'occupation_relation',
                'localTravelDetails',
                'spouse_details',
                'notes'
            ]
        )->find($ancestorData);

        //dd($ancestor?->localTravelDetails?->description);

        return view('page.ancestor-data.update', compact('ancestor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAncestorDataRequest  $request
     * @param  \App\Models\AncestorData  $ancestorData
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAncestorDataRequest $request, $ancestorData)
    {
        DB::beginTransaction();

        try {
            $ancestorData = AncestorData::find($ancestorData);

            if (!$ancestorData) {
                return response()->json([
                    "status" => false,
                    "message" => "AncestorData not found",
                ], 404);
            }

            $validatedData = $request->validated();

            // Remove notes, birth_details, and death_details from ancestor data update
            $ancestorDataInput = array_filter($validatedData, function ($key) {
                return !in_array($key, ['notes', 'birth_details', 'death_details']);
            }, ARRAY_FILTER_USE_KEY);

            // Update the ancestor data
            $ancestorData->update($validatedData);

            // Handle source of arrival logic
            if ($ancestorData->source_of_arrival == 1 || $ancestorData->source_of_arrival == 2) {
                $ancestorData->mode_of_travel_id = $request->input('mode_of_travel_id');
            } else {
                $this->updateTravelDetails($ancestorData, $validatedData, $request);
                $ancestorData->mode_of_travel_id = null;
            }

            $ancestorData->save();

            // Update spouse data
            $this->updateAncestorSpouse($ancestorData, $request);

            // Update ancestor notes, birth details, and death details
            $this->updateAncestorNotes($ancestorData->id, $validatedData);

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "AncestorData updated successfully",
                "redirectTo" => url("ancestor-data/{$ancestorData->id}")
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());

            return response()->json([
                "status" => false,
                "message" => "An error occurred while updating AncestorData. Please try again later.",
            ], 500);
        }
    }
    private function updateAncestorNotes($ancestorId, $validatedData)
    {
        // Find the existing note or create a new one if it doesn't exist
        $ancestorNote = AncestorNote::where('ancestor_id', $ancestorId)->first();

        if ($ancestorNote) {
            // Update the existing note, allowing null values
            $ancestorNote->update([
                'notes' => $validatedData['notes'] ?? null,
                'birth_details' => $validatedData['birth_details'] ?? null,
                'death_details' => $validatedData['death_details'] ?? null,
            ]);
        } else {
            // Create a new note if it doesn't exist, allowing null values
            AncestorNote::create([
                'ancestor_id' => $ancestorId,
                'notes' => $validatedData['notes'] ?? null,
                'birth_details' => $validatedData['birth_details'] ?? null,
                'death_details' => $validatedData['death_details'] ?? null,
            ]);
        }
    }
    private function updateAncestorSpouse($ancestorData, $request)
    {
        try {
            $spouseData = $request->only(['marriage_date', 'marriage_month', 'marriage_year', 'marriage_place', 'spouse_birth_place', 'spouse_death_place', 'spouse_family_name', 'spouse_given_name', 'spouse_birth_date', 'spouse_birth_month', 'spouse_birth_year', 'spouse_death_date', 'spouse_death_month', 'spouse_death_year']);
            //Log::info('Updating ancestor spouse data', $spouseData);

            $ancestorSpouse = AncestorSpouse::where('ancestor_id', $ancestorData->id)->first();

            if ($ancestorSpouse) {
                $ancestorSpouse->fill($spouseData);
            } else {
                $ancestorSpouse = new AncestorSpouse($spouseData);
                $ancestorSpouse->ancestor_id = $ancestorData->id;
            }

            $ancestorSpouse->save();

            //Log::info('Ancestor spouse data updated successfully', ['ancestor_spouse_id' => $ancestorSpouse->id]);
        } catch (\Exception $e) {
            Log::error('Error updating ancestor spouse data: ' . $e->getMessage());
            throw $e; // Rethrow the exception to trigger a rollback
        }
    }

    private function updateTravelDetails($ancestorData, $validatedData, $request)
    {
        // Check if a record already exists for the ancestor
        $travelDetailData = AncestorLocalTravelDetail::where('ancestor_id', $ancestorData->id)->first();

        // If no record exists, create a new one
        if (!$travelDetailData) {
            $travelDetailData = new AncestorLocalTravelDetail();
            $travelDetailData->ancestor_id = $ancestorData->id;
        }

        if (isset($validatedData['arrival_date_in_sa'])) {
            $travelDetailData->travel_date = Carbon::createFromFormat('Y-m-d', $validatedData['arrival_date_in_sa'])->format('Y-m-d');
        }

        if (isset($validatedData['evidence_of_arrival'])) {
            $travelDetailData->description = $validatedData['evidence_of_arrival'];
        }

        $travelDetailData->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AncestorData  $ancestorData
     * @return \Illuminate\Http\Response
     */
    public function destroy($ancestorData)
    {
        $ancestorData = AncestorData::find($ancestorData);
        $ancestorData->delete();
        return response()->json(["status" => true, "message" => "AncestorData Deleted Successfully"]);
    }
}
