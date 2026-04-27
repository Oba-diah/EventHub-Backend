<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
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
        $validated = $request->validate([
            'name'=>'required|string',
            'email'=>'required|email|unique:users,email',
            'phoneNumber'=>'nullable|string',
            'gender'=>'nullable|string',
            'dateOfBirth'=>'nullable|string',
            'location'=>'nullable|string',
            'role_id'=>'required|integer|exists:roles,id',
        ]);
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'] ? Hash::make($validated['password']) : Hash::make('Qwerty1234');
        $user->role_id = $validated['role_id'];
        $user->phoneNumber = $validated['phoneNumber'];
        $user->gender = $validated['gender'];
        $user->dateOfBirth = $validated['dateOfBirth'];
        $user->location = $validated['location'];
        

        $user->save();

        return response()->json(['message' => 'User Saved Successfully.'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $validated = $request->validate([
            'name'=>'required|string',
            'email'=>'required|email',
            'password'=>'nullable|string|min:8',
            'phoneNumber'=>'nullable|string',
            'gender'=>'nullable|string',
            'dateOfBirth'=>'nullable|string',
            'location'=>'nullable|string',
            'role_id'=>'required|integer|exists:roles,id',
        ]);
        $user = User::find($id);
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'] ? Hash::make($validated['password']) : $user->password;
        $user->role_id = $validated['role_id'];
        $user->phoneNumber = $validated['phoneNumber'];
        $user->gender = $validated['gender'];
        $user->dateOfBirth = $validated['dateOfBirth'];
        $user->location = $validated['location'];

        $user->save();

        return response()->json(['message' => 'User Updated Successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
