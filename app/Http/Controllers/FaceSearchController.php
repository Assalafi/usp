<?php

namespace App\Http\Controllers;

use App\Services\FaceSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FaceSearchController extends Controller
{
    public function search(Request $request, FaceSearchService $service)
    {
        if (!session()->has('log')) return response()->json(['message' => 'Your admin session has expired. Please sign in again.'], 401);
        $data = $request->validate([
            'photo' => 'required|file|image|mimes:jpeg,jpg,png|max:5120',
            'scope' => 'required|in:ug_students,ug_staff,ug_all',
        ]);
        try {
            $result = $service->search($request->file('photo'), $data['scope']);
            if (($result['indexed'] ?? 0) < 1) {
                throw ValidationException::withMessages(['photo' => 'The photo search index is not ready for this record type. Ask the administrator to run the face-search index command.']);
            }
            Log::info('UG administrator performed a photo search', ['username' => session('username'), 'scope' => $data['scope'], 'indexed' => $result['indexed']]);
            return response()->json($result);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['photo' => $exception->getMessage() ?: 'The photo could not be analysed. Please try a clearer, front-facing photo.']);
        }
    }
}
