<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // Authentifizierter Benutzer

        // Bilder des Benutzers abrufen
        $images = Image::all()->map(function ($image) {
            return [
                'id' => $image->id,
                'name' => $image->name,
                'type' => $image->type,
                'size' => $image->size,
                'path' => url('storage/' . $image->path), // Absolute URL generieren
            ];
        });

        return response()->json([
            'message' => 'Images retrieved successfully!',
            'images' => $images,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|max:10000', // Validiert die hochgeladenen Bilder
        ]);

        $user = $request->user(); // Authentifizierter Benutzer
        $uploadedImages = [];

        foreach ($request->file('images') as $file) {
            // Speichert die Datei
            $path = $file->store('uploads', 'public');

            // Speichert die Bilddaten in der Datenbank
            $image = Image::create([
                'user_id' => $user->id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            $uploadedImages[] = $image;
        }

        return response()->json([
            'message' => 'Images uploaded successfully!',
            'images' => $uploadedImages,
        ], 201);
    }

    public function show(Image $image)
    {
        //
    }

    public function update(Request $request, Image $image)
    {
        //
    }

    public function destroy(Image $image)
    {
        $user = request()->user();

        // Überprüfen, ob der Benutzer Eigentümer des Bildes ist
        if ($image->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Datei aus dem Speicher löschen
        Storage::disk('public')->delete($image->path);

        // Bild aus der Datenbank löschen
        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully!',
        ]);
    }
}
