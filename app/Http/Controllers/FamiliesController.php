<?php

namespace App\Http\Controllers;

use App\Models\families;
use App\Models\family_members;
use Illuminate\Http\Request;

class FamiliesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $families = families::all();
        return response()->json($families);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = \Validator::make($request->all(), [
            'family_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'father_id' => 'required|exists:family_members,member_id|unique:families,father_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $father = family_members::find($request->father_id);
        if (!$father || $father->gender !== 'male') {
            return response()->json(['message' => 'Father must be a male family member'], 422);
        }

        $family = families::create($request->all());
        return response()->json($family, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $family = families::find($id);
        if (!$family) {
            return response()->json(['message' => 'Family not found'], 404);
        }
        return response()->json($family);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, families $families)
    {
        //
        $families->update($request->all());
        return response()->json($families);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(families $families)
    {
        //
        $families->delete();
        return response()->json(null, 204); 
    }
}
