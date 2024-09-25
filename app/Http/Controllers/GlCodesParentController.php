<?php

namespace App\Http\Controllers;

use App\Models\GlCodesParent;

use Illuminate\Http\Request;

class GlCodesParentController extends Controller
{
    public function index()
    {
        $glCodesParents = GlCodesParent::get();

        return view('page.gl-codes-parent.index', compact('glCodesParents'));
    }

    // Show a single record
    public function show(GlCodesParent $gl_codes_parent)
    {
        //$glCodesParent = GlCodesParent::findOrFail($id);
        //return view('gl-codes-parent.show', compact('glCodesParent'));

        $html = view("models.parentgl-view", compact('gl_codes_parent'))->render();
        return response()->json(["status" => true, "html" => $html]);
    }

    // Create a new record (show the form)
    public function create()
    {
        $html = view("models.parentgl-create")->render();
        return response()->json(["status" => true, "html" => $html]);

        //return view('gl_codes_parent.create');
    }

    // Store a new record in the database
    public function store(Request $request)
    {
        // Validate input data
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable'
            // ... other validation rules
        ]);

        // Create a new GlCodesParent instance
        GlCodesParent::create($validatedData);

        // Redirect to the index page or show success message
        //return redirect()->route('gl-codes-parent.index');
        return response()->json([
            "status" => true,
            "message" => "Account Added Successfully",
            //"redirectTo" => url("gl-codes")
            "redirectTo" => route("gl-codes-parent.index")
        ]);
    }

    // Edit a record (show the form)
    public function edit($id)
    {
        $glCodesParent = GlCodesParent::findOrFail($id);
        //return view('gl-codes-parent.edit', compact('glCodesParent'));

        $html = view("models.parentgl-update", compact('glCodesParent'))->render();
        return response()->json(["status" => true, "html" => $html]);
    }

    // Update a record in the database
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
        ]);

        // Find the record by its ID
        $glCodesParent = GlCodesParent::findOrFail($id);

        // Update the record with the validated data
        $glCodesParent->update($validatedData);

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Record updated successfully',
            'redirectTo' => route('gl-codes-parent.index')
        ]);
    }


    // Delete a record
    public function destroy($id)
    {
        GlCodesParent::destroy($id);

        // Redirect to the index page or show success message
        return redirect()->route('gl-codes-parent.index');
    }
}
