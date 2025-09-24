<?php

    namespace App\Http\Controllers\Api\User;

    use App\Http\Controllers\Controller;
    use App\Http\Requests\User\UserReq;
    use App\Http\Resources\User\UserRes;
    use App\Http\Resources\User\UserResource;
    use App\Services\User\UserSer;

    class UserController extends Controller
    {
        protected $UserSer;

        public function __construct(UserSer $UserSer)
        {
            $this->UserSer = $UserSer;
        }

        public function index()
        {
            return UserRes::collection($this->UserSer->getAll());
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

        public function update(Userreq $request, $id)
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
