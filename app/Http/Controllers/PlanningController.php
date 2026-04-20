<?php

namespace App\Http\Controllers;

use App\Models\PublicWork;
use App\Models\LandUseSector;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index()
    {
        return view('planning.index');
    }

    public function getWorks()
    {
        $works = PublicWork::all();
        return response()->json($works);
    }

    public function storeWork(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'required|string',
            'budget' => 'nullable|numeric',
            'geometry_type' => 'nullable|string',
            'geometry_data' => 'nullable|string',
        ]);

        $work = PublicWork::create($validated);

        // Disparar WebSocket event para sincronización en tiempo real
        event(new \App\Events\PublicWorkRegistered($work));

        return response()->json([
            'message' => 'Obra registrada correctamente',
            'work' => $work
        ]);
    }

    public function updateWork(Request $request, $id)
    {
        $work = PublicWork::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'required|string',
            'budget' => 'nullable|numeric',
            'geometry_type' => 'nullable|string',
            'geometry_data' => 'nullable|string',
        ]);
        $work->update($validated);

        // Notificar cambios a otras pestañas
        if (class_exists(\App\Events\PublicWorkRegistered::class)) {
            event(new \App\Events\PublicWorkRegistered($work));
        }

        return response()->json($work);
    }

    public function deleteWork($id)
    {
        $work = PublicWork::findOrFail($id);
        $work->delete();

        // Puedes crear un PublicWorkDeleted, o enviar el mismo con status "deleted" para que se quite del JS
        $work->status = 'deleted';
        if (class_exists(\App\Events\PublicWorkRegistered::class)) {
            event(new \App\Events\PublicWorkRegistered($work));
        }
        
        return response()->json(['message' => 'Obra eliminada']);
    }

    public function uploadImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120' // Max 5MB
        ]);

        $work = PublicWork::findOrFail($id);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public_works_images', 'public');
            $work->image_url = \Illuminate\Support\Facades\Storage::url($path);
            $work->save();

            if (class_exists(\App\Events\PublicWorkRegistered::class)) {
                event(new \App\Events\PublicWorkRegistered($work));
            }

            return response()->json($work);
        }

        return response()->json(['error' => 'No image provided'], 400);
    }

    public function getSectors()
    {
        $sectors = LandUseSector::all();
        return response()->json($sectors);
    }
}
