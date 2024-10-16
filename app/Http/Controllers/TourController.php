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

class TourController extends Controller
{
    use ApiResponses;
    protected $checkAvailabilityService;

    public function __construct(CheckAvailabilityService $checkAvailabilityService)
    {
        $this->checkAvailabilityService = $checkAvailabilityService;
    }
    public function searchForTour(Request $request)
    {
        // Validate the search input (for example, search by name or ID)
        $request->validate([
            'search_term' => 'required|string',
        ]);

        // Search for the tour based on the search term (e.g., tour name or program name)
        $searchTerm = $request->input('search_term');

        // Example: Searching for a tour by name
        $tour = Tour::where('name', 'like', '%' . $searchTerm . '%')
            ->orWhereHas('program', function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            })
            ->with('program', 'tourists')  // Load related program and tourists
            ->first();  // Retrieve the first matching result

        // Check if the tour was found
        if (!$tour) {
            return $this->failed('No tour found with the provided search term.', 404);
        }

        // Return the tour details
        return $this->success($tour, 'Tour details retrieved successfully!', 200);
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
