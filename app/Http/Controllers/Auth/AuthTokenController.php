<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
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

    public function sendResetLink(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;

        // 1️⃣ Token generieren
        $token = Str::random(64);

        // 2️⃣ Token in password_resets speichern
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => bcrypt($token), // Laravel speichert hashed
                'created_at' => Carbon::now()
            ]
        );

        // 3️⃣ URL selbst zusammenbauen
        $resetUrl = config('app.frontend_url') . '/reset-password/?token=' . $token . '&email=' . urlencode($email);

        // 4️⃣ Mail selbst verschicken
        Mail::send('emails.password_reset', ['url' => $resetUrl], function($message) use ($email) {
            $message->to($email);
            $message->subject('Passwort zurücksetzen');
        });

        return response()->json(['message' => 'Reset-Link wurde gesendet.'], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = Password::reset(
            $validator->validated(),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 400);
    }
}
