<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;

class AuthTokenController extends Controller
{
    public function register(Request $request)
    {
        // Validierung der Eingabedaten
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // "confirmed" erwartet ein "password_confirmation"-Feld
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Verschlüsseltes Passwort hinzufügen
        $validatedData = $validator->validated();
        $validatedData['password'] = bcrypt($validatedData['password']);

        // Benutzer erstellen
        $user = User::create($validatedData);

        // Standard-Rolle "user" zuweisen
        $user->assignRole('user');

        // Token generieren
        $success['token'] = $user->createToken('chatApp')->plainTextToken;
        $success['user'] = new UserResource($user);

        // Antwort zurückgeben
        return response()->json([
            'user' => $success['user'],
            'token' => $success['token']
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        if (!auth()->attempt($validator->validated())) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 422);
        }

        $user = auth()->user();

        $success['token'] = $user->createToken('chatApp')->plainTextToken;
        $success['user'] = new UserResource($user);


        return response()->json([
            'user' => $success['user'],
            'token' => $success['token']
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ], 200);
    }
}
