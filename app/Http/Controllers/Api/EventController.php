<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Lister les événements filtrés par la ville de l'utilisateur connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();
        $perPage  = min($request->query('per_page', 15), 50);

        // Filtrage automatique : on ne retourne que les événements de la ville de l'utilisateur
        $events = Event::where('ville_id', $authUser->ville_id)
            ->with(['ville', 'regiment'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(EventResource::collection($events));
    }

    /**
     * Créer un événement.
     * Admin : type=ville ; Chef régiment : type=regiment pour son régiment.
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        // Vérification du scope via la Policy : type et régiment sont passés en argument
        $this->authorize('create', [
            Event::class,
            $request->type,
            $request->regiment_id,
        ]);

        $data               = $request->validated();
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('events/covers', 'public');
        }

        $event = Event::create($data);

        return response()->json(new EventResource($event->load(['ville', 'regiment'])), 201);
    }

    /**
     * Afficher un événement.
     * L'événement doit appartenir à la même ville que l'utilisateur.
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        $this->authorize('view', $event);
        $event->load(['ville', 'regiment', 'createdBy']);

        return response()->json(new EventResource($event));
    }

    /**
     * Mettre à jour un événement.
     * Admin : tous ; Chef régiment : ses événements de type 'regiment'.
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('events/covers', 'public');
        }

        $event->update($data);

        return response()->json(new EventResource($event->fresh(['ville', 'regiment'])));
    }

    /**
     * Supprimer un événement.
     */
    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        if ($event->cover_image) {
            Storage::disk('public')->delete($event->cover_image);
        }

        $event->delete();

        return response()->json(['message' => 'Événement supprimé.']);
    }

    /**
     * Lister les présences à un événement.
     * Admin ou chef régiment (pour les événements de son régiment).
     */
    public function attendance(Request $request, Event $event): JsonResponse
    {
        $this->authorize('viewAttendance', $event);

        $perPage    = min($request->query('per_page', 15), 50);
        $attendance = $event->attendances()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json(AttendanceResource::collection($attendance));
    }
}
