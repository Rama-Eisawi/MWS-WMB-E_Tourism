<?php

namespace App\Http\Controllers;

use App\Http\Requests\Program\CreateProgramRequest;
use App\Http\Requests\Program\UpdateProgramRequest;
use App\Models\Program;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    use ApiResponses;
    /**
     * Show all programs
     */
    public function index()
    {
        $programs = Program::all();
        return $this->success($programs, 'The List of Programs', 200);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Store a new program (Admin only)
     */
    public function store(CreateProgramRequest $request)
    {
        $program = Program::create($request->validated());
        return $this->success($program, 'Program created successfully', 201);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Show a specific program by ID
     */
    public function show($id)
    {
        $program = Program::findOrFail($id);
        return $this->success($program, 'Program info', 200);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Update an existing program (Admin only)
     */
    public function update(UpdateProgramRequest $request, $id)
    {
        $program = Program::findOrFail($id);
        $validated = $request->validated();
        $program->update($validated);
        return $this->success($program, 'Program updated successfully', 200);
    }
    //----------------------------------------------------------------------------------------------
    /**
     * Delete an existing program (Admin only)
     */
    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $program->delete();
        return $this->success(null, 'Program deleted successfully', 200);
    }
}
