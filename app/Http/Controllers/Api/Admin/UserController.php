<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserRes;
use App\Services\User\UserSer;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $UserSer;

    public function __construct(UserSer $UserSer)
    {
        $this->UserSer = $UserSer;
    }

    public function index(Request $request)
    {
        $filters = [
            'name' => $request->input('name'),
            'role' => $request->input('role'),
        ];

        // Ambil jumlah data per halaman dari query (default 10)
        $perPage = $request->input('per_page', 10);

        // Ambil halaman dari query (default 1)
        $page = $request->input('page', 1);

        // Ambil data user dari service
        $users = $this->UserSer->getAll($filters, $perPage, $page);

        // Return hasil dalam format JSON terstruktur
        return response()->json([
            'data' => UserRes::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string'
        ]);

        $user = $this->UserSer->create($data);

        return new UserRes($user);
    }

    public function show($id)
    {
        return new UserRes($this->UserSer->find($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|string'
        ]);

        $user = $this->UserSer->update($id, $data);

        return new UserRes($user);
    }

    public function destroy($id)
    {
        $this->UserSer->delete($id);

        return response()->json(['message' => 'User deleted successfully']);
    }
}
