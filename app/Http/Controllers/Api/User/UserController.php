<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserReq;
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
        $filters = $request->only(['name', 'role', 'per_page']);
        return UserRes::collection($this->UserSer->getAll($filters));
    }

    public function store(UserReq $request)
    {
        $user = $this->UserSer->create($request->validated());
        return new UserRes($user);
    }

    public function show($id)
    {
        return new UserRes($this->UserSer->find($id));
    }

    public function update(UserReq $request, $id)
    {
        $user = $this->UserSer->update($id, $request->validated());
        return new UserRes($user);
    }

    public function destroy($id)
    {
        $this->UserSer->delete($id);
        return response()->json(['message' => 'User deleted successfully']);
    }
}
