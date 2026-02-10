<?php

namespace App\Http\Controllers;

use App\Models\relations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RelationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(relations::with(['primaryMember', 'secondaryMember'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $validator = Validator::make($request->all(), [
            'primary' => 'required|exists:family_members,member_id',
            'secondary' => 'required|exists:family_members,member_id',
            'relationship_type' => 'required|in:father,mother,spouse',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        relations::create($validator->validated());

        return response()->json(['message' => 'Relation created successfully' ,
            "relation" => $validator->validated()
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $member_id)
    {
        $relations = relations::with(['primaryMember', 'secondaryMember'])
            ->where('primary', $member_id)
            ->orWhere('secondary', $member_id)
            ->get();

        if ($relations->isEmpty()) {
            return response()->json(['message' => 'No relations found for this member'], 404);
        }

        // $relation = $relations->map(function ($relation) use ($member_id) {
        //     if ($relation->primary === $member_id) {
        //         return [
        //             'related_member' => $relation->secondaryMember,
        //             'relationship_type' => $relation->relationship_type
        //         ];
        //     } else {
        //         return [
        //             'related_member' => $relation->primaryMember,
        //             'relationship_type' => $this->invertRelationshipType($relation->relationship_type)
        //         ];
        //     }
        // });

        // dd($relations);
        $relation = json_decode($relations);
        // dd($relation[0]->primary_member->first_name);

        $response = [];

        foreach($relation as $instance){
            $record = $instance->primary_member->first_name . " is " . $instance->relationship_type . " of " . $instance->secondary_member->first_name;
            $response[$instance->secondary_member->first_name] = $record;
        }

        return response()->json([
            "member" => $response
        ]);
    
    }

    public function invertRelationshipType($type)
    {
        $inverted = [
            'father' => 'child',
            'mother' => 'child',
            'spouse' => 'spouse'
        ];

        return $inverted[$type] ?? $type;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, relations $relations)
    {
        //

       $id = $request->get("relation_id");
       $validator = Validator::make($request->all(), [
            'primary' => 'sometimes|required|exists:family_members,member_id',
            'secondary' => 'sometimes|required|exists:family_members,member_id',
            'relationship_type' => 'sometimes|required|in:father,mother,spouse',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        relations::where("id", $id)->update($validator->validated());
        return response()->json(['message' => 'relation updated successfully',
                                       "member" => $validator->validated()], 
                                       200); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $relation_id )
    {
        //
        $toBeDeleted = relations::find($relation_id);

        if(!$toBeDeleted){
             return response()->json(['message' => 'Relationship not found'], 404);
        }

        $toBeDeleted->delete();

        return response()->json(['message' => 'relation deleted successfully',
        "relation" =>  $toBeDeleted
        ], 200);
    }
}
