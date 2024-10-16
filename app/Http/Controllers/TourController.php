<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tour\CreateTourRequest;
use App\Http\Requests\Tour\FilterToursRequest;
use App\Http\Requests\Tour\UpdateTourRequest;
use App\Models\Tour;
use App\Models\Tourist;
use App\Services\CheckAvailabilityService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TourController extends Controller
{
    use ApiResponses;
    protected $checkAvailabilityService;

    public function __construct(CheckAvailabilityService $checkAvailabilityService)
    {
        $this->checkAvailabilityService = $checkAvailabilityService;
    }
    public function reportToursByDriver(Request $request)
    {
        // Validate the input dates
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Get the date range from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query to count how many tours each driver conducted between the two dates
        $report = Tour::select('driver_id', DB::raw('COUNT(*) as total_tours'))
            ->whereBetween('start_date', [$startDate, $endDate])
            ->groupBy('driver_id')
            ->with('driver') // Eager load driver information
            ->get();

        // Check if any data was found
        if ($report->isEmpty()) {
            return $this->failed('No tours found within the specified date range.', 404);
        }

        // Return the report
        return $this->success($report, 'Report generated successfully.', 200);
    }
    //------------------------------------------------------------------------------------------------

    public function searchForTour(Request $request)
    {
        // Validate the search query
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Get the search query
        $searchQuery = $request->input('query');

        // Search for tours by ID or related program name, and include guide, driver info, and only available tours
        $tours = Tour::with(['program', 'guide', 'driver']) // Eager load guide, driver, and program relationships
            ->available() // Assuming available scope is defined
            ->where(function ($q) use ($searchQuery) {
                $q->where('id', $searchQuery) // Search by tour ID
                    ->orWhereHas('program', function ($q) use ($searchQuery) {
                        $q->where('name', 'LIKE', '%' . $searchQuery . '%'); // Search for related program names
                    });
            })
            ->get();

        // Check if any tours were found
        if ($tours->isEmpty()) {
            return $this->failed('No tours found matching the query.', 404);
        }

        // Return the results
        return $this->success($tours, 'Tours found', 200);
    }
    //------------------------------------------------------------------------------------------------
    public function registerInTour($id)
    {
        $user = auth()->user();  // Get the authenticated user

        // Check if the user is a tourist
        if ($user->role !== 'tourist') {
            return $this->failed('Only tourists are allowed to register for tours.', 403);
        }
        // Find the tour by ID and ensure it's available
        $tour = Tour::available()->findOrFail(id: $id);

        // Retrieve the tourist associated with the authenticated user
        $tourist = Tourist::where('user_id', $user->id)->first();

        // Check if the tourist is already registered for the same tour
        if (!is_null($tourist->tour_id) && $tourist->tour_id == $id) {

            return $this->failed('You are already registered for this tour.', 422);
        }
        // Check if the tourist is already registered for another tour
        if (!is_null($tourist->tour_id)) {
            Log:
            return $this->failed('You are already registered for another tour.', 422);
        }

        // Update the tourist's tour_id with the selected tour
        $tourist->update(['tour_id' => $id]);

        // Update the number of participants in the selected tour
        $tour->increment('number');
        return $this->success($tour, 'Tour registration successful!', 201);
    }
    //------------------------------------------------------------------------------------------------
    /**
     * Display a listing of the resource.
     */
    public function index(FilterToursRequest $request)
    {
        // Get the validated data from the request
        $validated = $request->validated();

        // Extract the start_date and end_date from the request, default to null if not provided
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        // Get all available tours within the specified date range (if provided)
        $availableTours = Tour::available($startDate, $endDate)
            ->with('program:id,name')  // Load the program's name
            ->get(['program_id', 'price', 'start_date', 'end_date']); // Select the fields to show from the tour


        return $this->success($availableTours, 'The list of Available Tours', 200);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTourRequest $request)
    {
        $validated = $request->validated();

        $guideId = $validated['guide_id'];
        $driverId = $validated['driver_id'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // Check availability of the guide
        if (!$this->checkAvailabilityService->isGuideAvailable($guideId, $startDate, $endDate)) {
            return $this->failed('Guide is not available for the selected dates', 422);
        }
        // Check availability of the driver
        if (!$this->checkAvailabilityService->isDriverAvailable($driverId, $startDate, $endDate)) {
            return $this->failed('Driver is not available for the selected dates', 422);
        }
        // Create new tour
        $tour = Tour::create($validated);
        return $this->success($tour, 'Tour created successfully.', 201);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tour = Tour::with('program:id,name')->findOrFail($id);
        return $this->success($tour, 'Tour info', 200);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTourRequest $request, $id)
    {
        #FIXME
        $tour = Tour::findOrFail($id);
        $validated = $request->validated();

        $newStartDate = $validated['start_date'] ?? $tour->start_date;
        $newEndDate = $validated['end_date'] ?? $tour->end_date;

        // Check availability only if new dates are provided
        if (isset($validated['start_date']) || isset($validated['end_date'])) {
            // Check availability of the guide in the new period
            if (
                isset($validated['guide_id']) &&
                !$this->checkAvailabilityService->isGuideAvailable($validated['guide_id'], $newStartDate, $newEndDate)
            ) {
                return response()->json(['error' => 'The selected guide is not available during this period.'], 400);
            }

            // Check availability of the driver in the new period
            if (
                isset($validated['driver_id']) &&
                !$this->checkAvailabilityService->isDriverAvailable($validated['driver_id'], $newStartDate, $newEndDate)
            ) {
                return response()->json(['error' => 'The selected driver is not available during this period.'], 400);
            }
        }

        // Update the tour with only the provided validated data
        $tour->update($validated);
        return $this->success($tour, 'Tour updated successfully.', 200);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tour = Tour::findOrFail($id);
        $tour->delete();
        return $this->success(null, 'Tour deleted successfully', 200);
    }
}
