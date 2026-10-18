<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Stuff;
use App\Models\Transaction;
use App\Services\BatchService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    protected $transaction;

    public function __construct(TransactionService $transaction, protected BatchService $batch)
    {
        $this->transaction = $transaction;
    }

    /**
     * Riwayat transaksi.
     *
     * Kasir hanya melihat transaksi sendiri, admin & gudang bisa semua.
     * Query ditulis langsung (bukan lewat service) supaya benar-benar
     * dipaginasi di database — bukan memuat 18 ribu baris ke memori HP.
     */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min((int) $request->query('per_page', 25), 100);

        $query = Transaction::with(['detail', 'user']);

        // Kasir (non-admin) dibatasi ke transaksinya sendiri.
        if (! $user->can('isAdmin') && ! $request->filled('id_user')) {
            $query->where('idUser', $user->idUser);
        }

        $query
            ->when($request->filled('id_user'), fn ($q) => $q->where('idUser', $request->query('id_user')))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->query('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->query('sampai')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status') ? 1 : 0))
            ->when($request->filled('q'), function ($q) use ($request)
            {
                $s = $request->query('q');
                $q->where(fn ($sub) => $sub->where('idPenjualan', 'like', "%{$s}%")->orWhere('namaUser', 'like', "%{$s}%"));
            })
            ->orderByDesc('idbaru');

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => TransactionResource::collection($paginator->items()),
            'meta' => [
                'total'              => $paginator->total(),
                'per_page'           => $paginator->perPage(),
                'halaman_sekarang'   => $paginator->currentPage(),
                'halaman_terakhir'   => $paginator->lastPage(),
                'ada_halaman_berikutnya' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * Simpan penjualan.
     *
     * Semua harga dihitung ulang dari database — tidak mempercayai
     * harga yang dikirim perangkat. Stok dikurangi otomatis oleh trigger
     * MySQL pada tabel `detail_penjualan` saat baris detail di-insert.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $user  = $request->user();
        $items = $request->items;

        // ---- Hitung dari data master, bukan dari input perangkat ----
        $stuffs = Stuff::whereIn('idBuku', array_column($items, 'id_buku'))
            ->get()
            ->keyBy('idBuku');

        $rincian = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $obat = $stuffs->get($item['id_buku']);

            if (!$obat) {
                return response()->json([
                    'message' => "Obat {$item['id_buku']} tidak ditemukan.",
                ], 422);
            }

            $jumlah   = (int) $item['jumlah'];
            $harga    = (int) $obat->hargaJual;
            $discPersen = (float) $obat->disc;
            $disc     = (int) round($jumlah * ($harga * $discPersen / 100));
            $ppn      = (float) site('ppn') / 100 * ($jumlah * $harga - $disc);

            if ($jumlah > $obat->stock) {
                return response()->json([
                    'message' => "Stok {$obat->obat} tidak cukup. Tersedia {$obat->stock}, diminta {$jumlah}.",
                    'errors'  => ['items' => "Stok {$obat->obat} tidak cukup."],
                    'kode'    => 'STOK_KURANG',
                ], 422);
            }

            $subtotal += ($harga * $jumlah) - $disc;

            // WAJIB di-key by idBuku: Eloquent memakai key array sebagai
            // id relasi saat attach(). Isi array = kolom pivot detail_penjualan.
            $rincian[$obat->idBuku] = [
                'obat'        => $obat->obat,
                'hargaPokok'  => (int) $obat->hargaPokok,
                'hargaJual'   => $harga,
                'disc'        => $disc,
                'ppn'         => $ppn,
                'jumlah'      => $jumlah,
            ];
        }

        $diskon     = (int) ($request->diskon ?? 0);
        $totalBayar = max($subtotal - $diskon, 0);

        // Bon (piutang): uang belum diterima, jadi tidak ada cek
        // "uang kurang" dan tidak ada kembalian.
        $bon        = $request->boolean('bon');
        $totalMasuk = $bon ? 0 : (int) $request->total_bayar;
        $kembalian  = $bon ? 0 : max($totalMasuk - $totalBayar, 0);

        if (! $bon && $totalMasuk < $totalBayar) {
            return response()->json([
                'message' => 'Uang pembayaran kurang dari total belanja.',
                'errors'  => ['total_bayar' => 'Uang pembayaran kurang dari total belanja.'],
                'kode'    => 'UANG_KURANG',
            ], 422);
        }

        // ---- Simpan dalam satu transaksi DB ----
        try {
            $saved = DB::transaction(function () use ($user, $request, $bon, $subtotal, $diskon, $totalBayar, $kembalian, $rincian) {

                $transaction = Transaction::create([
                    'idPenjualan' => $this->generateId(),
                    'idUser'      => $user->idUser,
                    'namaUser'    => $user->nama,
                    // Kolom `total`     = nominal setelah diskon (konsisten dgn web)
                    'total'       => $totalBayar,
                    // Kolom `grandtotal` = subtotal sebelum diskon (konsisten dgn web)
                    'grandtotal'  => $subtotal,
                    'diskon'      => $diskon,
                    'kembalian'   => $kembalian,
                    'ppn'         => 0,
                    'status'      => 1,
                    'tanggal'     => now(),
                ]);

                // Insert ke detail_penjualan -> trigger MySQL mengurangi stok buku.
                $transaction->stuffs()->attach($rincian);

                // Bon pelanggan dicatat SEKALIGUS dengan transaksinya —
                // kalau salah satu gagal, keduanya batal (atomic).
                if ($bon) {
                    \App\Models\Piutang::create([
                        'idPenjualan'   => $transaction->idPenjualan,
                        'idUser'        => $user->idUser,
                        'nama_user'     => $user->nama,
                        'nama_pelanggan' => (string) $request->nama_pelanggan,
                        'telepon'       => $request->telepon_pelanggan,
                        'nominal'       => $totalBayar,
                        'status'        => 1,
                        'tanggal'       => now(),
                    ]);
                }

                // Setelah itu, potong dari batch yang paling cepat
                // kedaluwarsanya (FEFO). Harus di dalam transaksi DB yang
                // sama supaya `buku.stock` dan `batch_obat.stokKeluar`
                // tidak pernah berbeda kalau ada kegagalan di tengah.
                //
                // `$rincian` di-key oleh idBuku (dibutuhkan Eloquent
                // untuk `attach()`), jadi peta jumlahnya disusun dari key.
                $jumlahPerBuku = [];

                foreach ($rincian as $idBuku => $isi) {
                    $jumlahPerBuku[$idBuku] = (int) $isi['jumlah'];
                }

                $this->batch->kurangiFefo($jumlahPerBuku);

                return $transaction->fresh();
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Gagal menyimpan transaksi.',
                'kode'    => 'GAGAL_SIMPAN',
            ], 500);
        }

        return response()->json([
            'message' => 'Transaksi berhasil disimpan.',
            'data'    => new TransactionResource(
                $saved->load(['detail', 'user'])
            ),
        ], 201);
    }

    /**
     * Detail transaksi (untuk layar receipt / riwayat).
     */
    /**
     * Batch tersedia untuk satu obat (urutan FEFO).
     *
     * Membantu kasir tahu batch mana yang keluar dan kapan berakhirnya.
     */
    public function batch(string $id): JsonResponse
    {
        $obat = Stuff::findOrFail($id);

        return response()->json([
            'data' => array_merge(
                [
                    'id'    => $obat->idBuku,
                    'obat'  => $obat->obat,
                    'stock' => (int) $obat->stock,
                ],
                $this->batch->ringkasan($id)
            ),
        ]);
    }

    /**
     * Cek apakah sisa seluruh batch sama dengan `buku.stock`.
     *
     * Hasil kosong berarti data sehat. Endpoint ini sengaja terbuka untuk
     * semua user login supaya masalah ketahuan sebelum menjadi besar.
     */
    public function cekBatch(Request $request): JsonResponse
    {
        $beda = $this->batch->cekKonsistensi(
            min((int) $request->query('limit', 50), 500)
        );

        return response()->json([
            'sehat' => count($beda) === 0,
            'pesan' => count($beda) === 0
                ? 'Seluruh sisa batch cocok dengan stok buku.'
                : count($beda) . ' obat selisihnya tidak cocok.',
            'data'  => $beda,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $transaction = Transaction::with(['detail', 'user'])->findOrFail($id);

        return response()->json(['data' => new TransactionResource($transaction)]);
    }

    /**
     * Batalkan transaksi + kembalikan stok.
     *
     * Sengaja tidak memakai TransactionService::cancel() karena relasi
     * `ppn()` di model masih kosong (sudah dikomentari) sehingga pemanggilannya fatal error.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::with(['detail'])->findOrFail($id);

        if (!$transaction->status) {
            return response()->json(['message' => 'Transaksi ini sudah dibatalkan.'], 422);
        }

        $hariIni = now()->toDateString();
        $tanggal = \Illuminate\Support\Carbon::parse($transaction->tanggal)->toDateString();

        // Aturan sama dengan web: transaksi sudah lewat tanggal tidak bisa dibatalkan.
        if ($hariIni !== $tanggal && !optional($request->user())->can('isAdmin')) {
            return response()->json([
                'message' => 'Transaksi tanggal sebelumnya tidak bisa dibatalkan.',
            ], 422);
        }

        DB::transaction(function () use ($transaction) {
            // Kembalikan stok ke BATCH (FEFO) sekaligus ke buku.
            //
            // `buku.stock` dinaikkan manual karena trigger MySQL hanya
            // berjalan pada INSERT `detail_penjualan`, sedangkan di sini
            // baris detail tidak dihapus.
            $this->batch->kembalikanFefo(
                $transaction->detail
                    ->whereNotNull('idBuku')
                    ->mapWithKeys(fn ($d) => [$d->idBuku => (int) $d->jumlah])
                    ->all()
            );

            foreach ($transaction->detail as $detail) {
                if ($detail->idBuku) {
                    Stuff::where('idBuku', $detail->idBuku)->increment('stock', (int) $detail->jumlah);
                }
            }

            $transaction->status = 0;
            $transaction->save();
        });

        return response()->json([
            'message' => 'Transaksi dibatalkan dan stok dikembalikan.',
            'data'    => new TransactionResource($transaction->fresh()->load(['detail'])),
        ]);
    }

    /**
     * Nomor transaksi.
     *
     * Format dipertahankan sama dengan web: A#####-######-ddmmyy
     * (supaya format lama & laporan tidak pecah), tapi memakai random_int
     * dan dicek ulang terhadap database untuk menghindari tabrakan.
     */
    protected function generateId(): string
    {
        do {
            $id = sprintf(
                'A%05d-%06d-%s',
                random_int(0, 99999),
                random_int(0, 999999),
                now()->format('dmY')
            );
        } while (Transaction::where('idPenjualan', $id)->exists());

        return $id;
    }
}