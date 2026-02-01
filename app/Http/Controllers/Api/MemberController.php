<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::with(['office', 'creator']);
        
        //filter by office
        if ($request->has('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        //filter by status
        if ($request->has('status_aktif')) {
            $query->where('status_aktif', $request->status_aktif);
        }

        //search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orwhere('no_hp', 'like', "%{$search}%");
            });
        }

        $members = $query->latest()->paginate(20);
        
        return response()->json($members);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated =  $request->validate([
            'no_hp' => 'required|string|nuique:members,no_hp|max:15',
            'office_id' => 'required|exists:offices,id',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'asal_sekolah' => 'required|string|max:255',
            'tanggal_mulai_magang' => 'required|date',
            'tanggal_selesai_magang' => 'nullable|date|after_or_equal:tanggal_mulai_magang',
            'status_aktif' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $member = Member::create($validated);
        $member->load('office');

        return response()->json([
            'message' => 'Member berhasil dibuat',
            'data' => $member
        ], 201
        );

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
