<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Piutang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Daftar bon pelanggan (piutang).
 *
 * Kasir melihat bon yang dicatatnya sendiri; admin & gudang melihat
 * semuanya — sama seperti pembatasan pada riwayat transaksi.
 */
class PiutangController extends Controller
{
    /**
     * GET /api/piutang?status=1|0&q=...&page=...
     *
     * `status` kosong = semua. Ringkasan sisa piutang selalu dihitung
     * dari seluruh bon yang belum lunas pada cakupan user.
     */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min((int) $request->query('per_page', 25), 100);

        $lihatSemua = $user->can('isAdmin') || $user->can('isAdminGudang');

        $query = Piutang::query()
            ->when(! $lihatSemua, fn ($q) => $q->where('idUser', $user->idUser))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status') ? 1 : 0))
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = $request->query('q');
                $q->where(fn ($sub) => $sub
                    ->where('nama_pelanggan', 'like', "%{$s}%")
                    ->orWhere('idPenjualan', 'like', "%{$s}%"));
            })
            ->orderByDesc('id');

        $paginator = $query->paginate($perPage);

        $sisa = Piutang::query()
            ->when(! $lihatSemua, fn ($q) => $q->where('idUser', $user->idUser))
            ->where('status', 1)
            ->selectRaw('COUNT(*) AS jml, COALESCE(SUM(nominal), 0) AS total')
            ->first();

        return response()->json([
            'data' => collect($paginator->items())
                ->map(fn ($r) => $this->bentuk($r))
                ->all(),
            'ringkasan' => [
                'jumlah_belum_lunas' => (int) ($sisa->jml ?? 0),
                'total_belum_lunas'  => (int) ($sisa->total ?? 0),
            ],
            'meta' => [
                'total'            => $paginator->total(),
                'per_page'         => $paginator->perPage(),
                'halaman_sekarang' => $paginator->currentPage(),
                'halaman_terakhir' => $paginator->lastPage(),
                'ada_halaman_berikutnya' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * POST /api/piutang/{id}/lunas — catat pelunasan bon.
     *
     * Hanya admin atau kasir yang mencatat bon boleh melunasi.
     */
    public function lunas(Request $request, int $id): JsonResponse
    {
        $bon = Piutang::findOrFail($id);
        $user = $request->user();

        if (! $user->can('isAdmin') && $bon->idUser !== $user->idUser) {
            return response()->json([
                'message' => 'Hanya admin atau kasir pencatat bon yang boleh melunasi.',
            ], 403);
        }

        if (! $bon->status) {
            return response()->json(['message' => 'Bon ini sudah lunas.'], 422);
        }

        $bon->update([
            'status'        => 0,
            'tanggal_lunas' => now(),
        ]);

        return response()->json([
            'message' => 'Bon dilunasi.',
            'data'    => $this->bentuk($bon->fresh()),
        ]);
    }

    protected function bentuk(Piutang $r): array
    {
        return [
            'id'             => (string) $r->id,
            'id_penjualan'   => $r->idPenjualan,
            'nama_user'      => $r->nama_user,
            'nama_pelanggan' => $r->nama_pelanggan,
            'telepon'        => $r->telepon,
            'nominal'        => (int) $r->nominal,
            'lunas'          => ! $r->status,
            'tanggal'        => optional($r->tanggal)->toDateTimeString(),
            'tanggal_lunas'  => optional($r->tanggal_lunas)->toDateTimeString(),
        ];
    }
}
