<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCollectionRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\MediaResource;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::paginate(10));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified user with their media collection.
     */
    public function show(User $user): UserResource
    {
        $user->load('media');

        return new UserResource($user);
    }

    /**
     * Display a user's personal media collection.
     */
    public function collection(User $user): JsonResponse
    {
        $user->load('media');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'collection' => MediaResource::collection($user->media),
        ], 200);
    }

    /**
     * Add a media item to the user's personal collection.
     */
    public function addToCollection(AddToCollectionRequest $request, User $user): JsonResponse
    {
        $mediaId = (int) $request->validated()['media_id'];

        if ($user->media()->where('media_id', $mediaId)->exists()) {
            return response()->json([
                'message' => 'Media sudah ada di koleksi',
            ], 409);
        }

        $user->media()->attach($mediaId);
        $user->load('media');

        return response()->json([
            'message' => 'Media berhasil ditambahkan ke koleksi',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Remove a media item from the user's personal collection.
     */
    public function removeFromCollection(User $user, Media $media): JsonResponse
    {
        if (! $user->media()->where('media_id', $media->id)->exists()) {
            return response()->json([
                'message' => 'Media tidak ditemukan di koleksi pengguna',
            ], 404);
        }

        $user->media()->detach($media->id);
        $user->load('media');

        return response()->json([
            'message' => 'Media berhasil dihapus dari koleksi',
            'data' => new UserResource($user),
        ], 200);
    }
}
