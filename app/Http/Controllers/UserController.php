<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserController extends Controller
{

    protected $user;

    public function __construct(UserService $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        return view('user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->user->storeData($request);

        return redirect('user')->with('success', 'Sukses Menambahkan Pengguna');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param   $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $this->user->updateData($id, $request);

        return response()->json(['success' => 'Sukses Mengedit Pengguna']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param   $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        $this->user->deleteData($id);

        return response()->json(['success' => 'Sukses Menghapus Pengguna']);
    }

    // Change Password
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->user->updatePassword($request->password);
        return back()->with('success', 'Sukses Mengganti Password');
    }

    // Get Datatables
    public function datatables(): Object
    {
        return $this->user->getDatatables();
    }

    // Get Datatables
    public function select(UserRepository $userRepo, Request $request): Object
    {
        return $userRepo->select($request->nama);
    }
}
