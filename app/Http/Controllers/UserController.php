<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Hobbies;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $request->validate(['search' => ['nullable', 'string', 'max:100']]);

        $users = User::isActive(true, 'id', 'DESC')
            ->searchName($request->search)
            ->with('hobbies')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(StoreUserRequest $request)
    {

        DB::beginTransaction();

        try {

            $user = User::create($request->safe()->except('hobbies'));

            $this->storeHobbies($user, $request->validated('hobbies', []));

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User berhasil ditambahkan.');

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Gagal menambahkan user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput($request->except('password'))
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit(User $user)
    {
        $user->load('hobbies');

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();

        try {

            $dataUpdate = $request->safe()->except('hobbies', 'password');

            if ($request->filled('password')) {
                $dataUpdate['password'] = $request->validated('password');
            }

            $user->update($dataUpdate);

            $this->updateHobbies($user, $request->validated('hobbies'));

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User berhasil diupdate.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal mengubah user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput($request->except('password'))
                ->with('error', 'Terjadi kesalahan saat update data. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {

            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'User dan hobinya berhasil dihapus.');

        } catch (\Throwable $e) {

            Log::error('Gagal menghapus user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->with('error', 'Terjadi kesalahan saat menghapus data. Silakan coba lagi.');

        }
    }

    public function storeHobbies(User $user, array $hobbies): void
    {
        foreach ($hobbies as $hobby) {
            Hobbies::create([
               'user_id' => $user->id,
               'name' => $hobby['name'],
            ]);
        }
    }

    private function updateHobbies(User $user, array $hobbies): void
    {
        $keepIds = [];

        foreach ($hobbies as $item) {

            if (! empty($item['id'])) {
                $hobby = $user->hobbies()->find($item['id']);

                if ($hobby) {
                    $hobby->update(['name' => $item['name']]);
                    $keepIds[] = $hobby->id;
                    continue;
                }
            }

            // Hobi baru -> insert
            $keepIds[] = $user->hobbies()->create(['name' => $item['name']])->id;
        }

        // Hobi lama yang tidak ada di daftar -> hapus
        $user->hobbies()->whereNotIn('id', $keepIds)->delete();
    }
}
