<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QueueLoket;
use App\Models\QueueAntrian;
use App\Models\QueuePanggilan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AntrianController extends Controller
{
    /**
     * Display the main antrian page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('antrian.index');
    }

    /**
     * Display the nomor antrian page.
     *
     * @return \Illuminate\View\View
     */
    public function nomor()
    {
        $lokets = QueueLoket::where('is_active', true)->get();
        return view('antrian.nomor', compact('lokets'));
    }

    /**
     * Display the panggilan antrian page.
     *
     * @return \Illuminate\View\View
     */
    public function panggilan()
    {
        $lokets = QueueLoket::where('is_active', true)->get();
        return view('antrian.panggilan', compact('lokets'));
    }

    /**
     * Display the monitor antrian page.
     *
     * @return \Illuminate\View\View
     */
    public function monitor()
    {
        $lokets = QueueLoket::where('is_active', true)->get();
        return view('antrian.monitor', compact('lokets'));
    }

    /**
     * Display the setting antrian page.
     *
     * @return \Illuminate\View\View
     */
    public function setting()
    {
        $lokets = QueueLoket::where('is_active', true)->get();
        return view('antrian.setting', compact('lokets'));
    }

    /**
     * Get the list of loket.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoket()
    {
        $lokets = QueueLoket::where('is_active', true)
            ->select('id', 'no_loket', 'nama_loket')
            ->get();

        return response()->json($lokets);
    }

    /**
     * Generate a new queue number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateAntrian()
    {
        $today = Carbon::now()->format('Y-m-d');

        // Get the last queue number for today
        $lastAntrian = QueueAntrian::where('tanggal', $today)
            ->orderBy('no_antrian', 'desc')
            ->first();

        // Generate new queue number
        if ($lastAntrian) {
            $newNumber = str_pad((int)$lastAntrian->no_antrian + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        // Create new queue entry
        $antrian = new QueueAntrian();
        $antrian->tanggal = $today;
        $antrian->no_antrian = $newNumber;
        $antrian->status = '0';
        $antrian->save();

        return response()->json([
            'success' => true,
            'no_antrian' => $newNumber
        ]);
    }

    /**
     * Get the next queue number for a specific loket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextAntrian(Request $request)
    {
        $loket = $request->input('loket');
        $today = Carbon::now()->format('Y-m-d');

        // Get the next queue that hasn't been called
        $nextAntrian = QueueAntrian::where('tanggal', $today)
            ->where('status', '0')
            ->orderBy('no_antrian', 'asc')
            ->first();

        if ($nextAntrian) {
            // Update status to called
            $nextAntrian->status = '1';
            $nextAntrian->updated_date = Carbon::now();
            $nextAntrian->save();

            // Create a new panggilan entry
            $panggilan = new QueuePanggilan();
            $panggilan->antrian = $nextAntrian->no_antrian;
            $panggilan->loket = $loket;
            $panggilan->save();

            return response()->json([
                'success' => true,
                'no_antrian' => $nextAntrian->no_antrian,
                'loket' => $loket
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada antrian yang tersedia'
        ]);
    }

    /**
     * Get the current queue status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAntrianStatus()
    {
        $today = Carbon::now()->format('Y-m-d');

        // Get the latest called queue for each loket
        $latestCalls = QueuePanggilan::select('loket', DB::raw('MAX(id) as id'))
            ->groupBy('loket')
            ->get()
            ->map(function ($item) {
                return QueuePanggilan::find($item->id);
            });

        // Get total and remaining queues
        $totalAntrian = QueueAntrian::where('tanggal', $today)->count();
        $remainingAntrian = QueueAntrian::where('tanggal', $today)
            ->where('status', '0')
            ->count();
        $processedAntrian = $totalAntrian - $remainingAntrian;

        return response()->json([
            'success' => true,
            'latest_calls' => $latestCalls,
            'total' => $totalAntrian,
            'remaining' => $remainingAntrian,
            'processed' => $processedAntrian
        ]);
    }

    /**
     * Save loket settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSetting(Request $request)
    {
        $request->validate([
            'lokets' => 'required|array',
            'lokets.*.no_loket' => 'required|string',
            'lokets.*.nama_loket' => 'required|string',
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            // Get existing lokets
            $existingLokets = QueueLoket::all()->keyBy('id')->toArray();
            $updatedLoketIds = [];

            // Update or create lokets
            foreach ($request->input('lokets') as $loketData) {
                if (isset($loketData['id']) && !empty($loketData['id'])) {
                    // Update existing loket
                    $loket = QueueLoket::find($loketData['id']);
                    if ($loket) {
                        $loket->update([
                            'no_loket' => $loketData['no_loket'],
                            'nama_loket' => $loketData['nama_loket'],
                            'is_active' => true,
                        ]);
                        $updatedLoketIds[] = $loket->id;
                    }
                } else {
                    // Create new loket
                    $loket = QueueLoket::create([
                        'no_loket' => $loketData['no_loket'],
                        'nama_loket' => $loketData['nama_loket'],
                        'is_active' => true,
                    ]);
                    $updatedLoketIds[] = $loket->id;
                }
            }

            // Delete lokets that were not updated
            foreach ($existingLokets as $id => $loket) {
                if (!in_array($id, $updatedLoketIds)) {
                    QueueLoket::destroy($id);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan loket berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengaturan loket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a new loket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLoket(Request $request)
    {
        $request->validate([
            'no_loket' => 'required|string',
            'nama_loket' => 'required|string',
        ]);

        try {
            $loket = QueueLoket::create([
                'no_loket' => $request->input('no_loket'),
                'nama_loket' => $request->input('nama_loket'),
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loket berhasil ditambahkan',
                'loket' => $loket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan loket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a loket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLoket(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        try {
            $loket = QueueLoket::find($request->input('id'));

            if (!$loket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loket tidak ditemukan'
                ], 404);
            }

            $loket->delete();

            return response()->json([
                'success' => true,
                'message' => 'Loket berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus loket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * We don't need the uploadLogo method anymore since we're not storing logo in the database
     */
}
