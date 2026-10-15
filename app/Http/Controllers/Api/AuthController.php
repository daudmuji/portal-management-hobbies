<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {

            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (! $token = auth('api')->attempt($credentials)) {
                return $this->respond(false, 'Email atau password salah', null, 401);
            }

            return $this->respondWithToken($token);

        } catch (ValidationException $e) {

            return $this->respond(false, 'Data yang dikirim tidak valid', $e->errors(), 422);

        } catch (JWTException $e) {

            Log::error('Gagal membuat token saat login', [
                'message' => $e->getMessage(),
            ]);

            return $this->respond(false, 'Gagal memproses autentikasi, silakan coba lagi', null, 500);

        } catch (\Throwable $e) {

            Log::error('Gagal login', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->respond(false, 'Internal server error', null, 500);
        }
    }

    public function me()
    {
        try {

            $user = auth('api')->userOrFail();

            return $this->respond(true, 'Data user berhasil diambil', $user);

        } catch (TokenExpiredException $e) {

            return $this->respond(false, 'Token sudah kedaluwarsa', null, 401);

        } catch (TokenInvalidException $e) {

            return $this->respond(false, 'Token tidak valid', null, 401);

        } catch (JWTException $e) {

            return $this->respond(false, 'Token tidak ditemukan', null, 401);

        } catch (\Throwable $e) {

            Log::error('Gagal mengambil data user', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->respond(false, 'Internal server error', null, 500);
        }
    }

    public function logout()
    {
        try {

            auth('api')->logout();

            return $this->respond(true, 'Berhasil logout');

        } catch (TokenExpiredException $e) {

            return $this->respond(false, 'Token sudah kedaluwarsa', null, 401);

        } catch (TokenInvalidException $e) {

            return $this->respond(false, 'Token tidak valid', null, 401);

        } catch (JWTException $e) {

            return $this->respond(false, 'Token tidak ditemukan', null, 401);

        } catch (\Throwable $e) {

            Log::error('Gagal logout', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->respond(false, 'Internal server error', null, 500);
        }
    }

    public function refresh()
    {
        try {

            return $this->respondWithToken(auth('api')->refresh());

        } catch (TokenExpiredException $e) {

            return $this->respond(false, 'Token sudah kedaluwarsa, silakan login kembali', null, 401);

        } catch (TokenInvalidException $e) {

            return $this->respond(false, 'Token tidak valid', null, 401);

        } catch (JWTException $e) {

            return $this->respond(false, 'Gagal memperbarui token', null, 401);

        } catch (\Throwable $e) {

            Log::error('Gagal refresh token', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->respond(false, 'Internal server error', null, 500);
        }
    }

    protected function respondWithToken(string $token)
    {
        return $this->respond(true, 'Berhasil login', [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
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
