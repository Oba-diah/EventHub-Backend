<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'phoneNumber' => 'nullable|string',
            'gender' => 'nullable|string',
            'dateOfBirth' => 'nullable|string',
            'location' => 'nullable|string',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = isset($validated['password'])
            ? Hash::make($validated['password'])
            : Hash::make('Qwerty1234');
        $user->role_id = $validated['role_id'];
        $user->phoneNumber = $validated['phoneNumber'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->dateOfBirth = $validated['dateOfBirth'] ?? null;
        $user->location = $validated['location'] ?? null;

        $user->save();

        return response()->json(['message' => 'User created successfully']);
    }

    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'nullable|string|min:6',
            'phoneNumber' => 'nullable|string',
            'gender' => 'nullable|string',
            'dateOfBirth' => 'nullable|string',
            'location' => 'nullable|string',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $user = User::findOrFail($id);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = isset($validated['password'])
            ? Hash::make($validated['password'])
            : $user->password;

        $user->role_id = $validated['role_id'];
        $user->phoneNumber = $validated['phoneNumber'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->dateOfBirth = $validated['dateOfBirth'] ?? null;
        $user->location = $validated['location'] ?? null;

        $user->save();

        return response()->json(['message' => 'User updated successfully']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}