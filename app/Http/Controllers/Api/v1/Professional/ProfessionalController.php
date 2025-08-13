<?php

namespace App\Http\Controllers\Api\v1\Professional;

use App\Http\Controllers\Controller;
use App\Http\Requests\Professional\ProfessionalFilterRequest;
use App\Models\Professional;

class ProfessionalController extends Controller
{
    public function index(ProfessionalFilterRequest $request)
    {
        $professional = Professional::when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })->when($request->filled('order'), function ($query) use ($request) {
            $query->orderBy('name', $request->order);
        })->limit(empty($request->count) ? 10 : $request->count)
            ->offset(empty($request->page) ? 0 : $request->page * 5)
            ->get(['id', 'name', 'specialty']);

        return response([
            'status' => true,
            'message' => "List of Professionals",
            'data' => $professional
        ]);
    }
}
