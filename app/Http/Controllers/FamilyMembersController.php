<?php

namespace App\Http\Controllers;

use App\Models\family_members;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FamilyMembersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(family_members::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
       $validator =  Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'family_name' => 'nullable|string|max:255',
            "gender" => "required|in:male,female",
            "date_of_birth" => "required|date",
            "date_of_death" => "nullable|date|after_or_equal:date_of_birth",
            "photo" => "nullable|image|max:2048"
        ]);



        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated(); 
        // dd($validatedData);

        if($request->file('photo')){
            $validatedData['photo'] = $request->file('photo')->store('family_photos', 'public');
        }

        family_members::create($validatedData);

        return response()->json(['message' => 'Family member created successfully',
                                       "member" => $validatedData], 
                                       201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $member_id)
    {
        $family_member = family_members::find($member_id);

        if (!$family_member) {
            return response()->json(['message' => 'Family member not found'], 404);
        }

        return response()->json($family_member);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $member_id)
    {
        $id = $request->get("member_id");
       $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'family_name' => 'sometimes|nullable|string|max:255',
            "gender" => "sometimes|required|in:male,female",
            "date_of_birth" => "sometimes|required|date",       
            "photo" => "sometimes|nullable|image|max:2048",
            "date_of_death" => "sometimes|nullable|date|after_or_equal:date_of_birth",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        family_members::where("member_id", $id)->update($validator->validated());
        return response()->json(['message' => 'Family member updated successfully',
                                       "member" => $validator->validated()], 
                                       200);    
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $member_id)
    {
        $family_member = family_members::find($member_id);

        if (!$family_member) {
            return response()->json(['message' => 'Family member not found'], 404);
        }

        $family_member->delete();
        return response()->json(['message' => 'Family member deleted successfully',
            "member" => $family_member
        ], 200);
    }
}
