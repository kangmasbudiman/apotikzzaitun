<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Permintaan ke /api/* selalu dibalas JSON, apa pun kesalahannya,
        // supaya aplikasi mobile tidak pernah menerima halaman HTML error.
        $this->renderable(function (Throwable $e, Request $request) {

            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            // Biarkan framework yang menangani dua kasus ini — balasannya
            // sudah benar (422 + daftar errors per field).
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            if ($e instanceof NotFoundHttpException) {
                // Bedakan "data tidak ada" (mis. obat dihapus di web selagi
                // app masih menyimpan id-nya) vs endpoint yang salah.
                $dataTidakAda = $e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException;

                return response()->json([
                    'message' => $dataTidakAda
                        ? 'Data tidak ditemukan.'
                        : 'Endpoint tidak ditemukan.',
                ], 404);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'message' => 'Token tidak ada atau sudah kadaluarsa. Silakan login ulang.',
                ], 401);
            }

            // Middleware `can:` melempar AccessDeniedHttpException yang
            // membungkus AuthorizationException — keduanya harus ditangkap.
            if ($e instanceof AccessDeniedHttpException || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'message' => 'Akun Anda tidak punya akses ke fitur ini.',
                ], 403);
            }

            // Error lain yang memang HTTP (429 throttle, dll) tetap boleh
            // membawa pesan aslinya, tapi dibersihkan dari trace.
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Permintaan gagal diproses.',
                ], $e->getStatusCode(), $e->getHeaders());
            }

            // PENTING: untuk endpoint API, jangan pernah kirim stack trace ke
            // perangkat meski APP_DEBUG=true — hanya pesan generik. Detailnya
            // tetap tercatat penuh di storage/logs untuk dicariDeveloper.
            return response()->json([
                'message' => 'Terjadi kesalahan di server. Silakan coba lagi.',
            ], 500);
        });
    }
}
