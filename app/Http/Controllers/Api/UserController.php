<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Hobbies;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = User::with('hobbies')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return $this->respond(true, 'Data user berhasil diambil', UserResource::collection($users));
    }

    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();

        try {

            $user = User::create($request->safe()->except('hobbies'));

            $this->storeHobbies($user, $request->validated('hobbies', []));

            DB::commit();

            return $this->respond(true, 'User berhasil ditambahkan', new UserResource($user), 201);

        } catch (QueryException $e) {
            DB::rollBack();

            Log::error('Query error saat menambahkan user', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return $this->respond(false, 'Terjadi kesalahan pada database', null, 500);

        } catch (ConnectionException $e) {
            DB::rollBack();

            Log::error('Timeout saat menambahkan user', [
                'message' => $e->getMessage(),
            ]);

            return $this->respond(false, 'Request timeout, silakan coba lagi', null, 408);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menambahkan user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->respond(false, 'Internal server error', null, 500);
        }
    }

    public function show(User $user)
    {
        return $this->respond(true, 'Detail user berhasil diambil', new UserResource($user->load('hobbies')));
    }

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

            return $this->respond(true, 'User berhasil diperbarui', new UserResource($user));

        } catch (QueryException $e) {
            DB::rollBack();

            Log::error('Query error saat mengubah user', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return $this->respond(false, 'Terjadi kesalahan pada database', null, 500);

        } catch (ConnectionException $e) {
            DB::rollBack();

            Log::error('Timeout saat mengubah user', [
                'message' => $e->getMessage(),
            ]);

            return $this->respond(false, 'Request timeout, silakan coba lagi', null, 408);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal mengubah user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->respond(false, 'Internal server error', null, 500);
        }
    }

    public function destroy(User $user)
    {
        DB::beginTransaction();

        try {

            $user->delete();

            DB::commit();

            return $this->respond(true, 'User dan hobinya berhasil dihapus');

        } catch (QueryException $e) {
            DB::rollBack();

            Log::error('Query error saat menghapus user', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return $this->respond(false, 'Terjadi kesalahan pada database', null, 500);

        } catch (ConnectionException $e) {
            DB::rollBack();

            Log::error('Timeout saat menghapus user', [
                'message' => $e->getMessage(),
            ]);

            return $this->respond(false, 'Request timeout, silakan coba lagi', null, 408);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menghapus user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->respond(false, 'Internal server error', null, 500);
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

            $keepIds[] = $user->hobbies()->create(['name' => $item['name']])->id;
        }

        $user->hobbies()->whereNotIn('id', $keepIds)->delete();
    }


    private function respond(bool $status, string $message, $data = null, int $code = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
