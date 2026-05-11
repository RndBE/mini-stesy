<?php

namespace App\Http\Controllers;

use App\Models\NotificationHistory;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotifikasiController extends Controller
{
    public function index()
    {
        // Hanya superadmin
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403);
        }

        // User yang punya FCM token aktif
        $usersWithToken = DB::table('t_user as u')
            ->join('fcm_tokens as ft', 'ft.user_id', '=', 'u.id_user')
            ->where(function ($q) {
                $q->whereNull('u.status')->orWhere('u.status', 'aktif');
            })
            ->select('u.id_user', 'u.nama', 'u.username', 'u.level_user', 'u.instansi_id')
            ->groupBy('u.id_user', 'u.nama', 'u.username', 'u.level_user', 'u.instansi_id')
            ->orderBy('u.nama')
            ->get();

        // History notifikasi terbaru
        $histories = NotificationHistory::with('sender')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('notifikasi.index', [
            'title'          => 'Kirim Notifikasi',
            'usersWithToken' => $usersWithToken,
            'histories'      => $histories,
        ]);
    }

    public function send(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'title'          => 'required|string|max:255',
            'body'           => 'required|string|max:1000',
            'recipient_type' => 'required|in:all,selected',
            'recipient_ids'  => 'required_if:recipient_type,selected|array',
            'recipient_ids.*'=> 'integer',
        ]);

        $fcm = new FcmService();
        $title = $request->title;
        $body  = $request->body;
        $data  = ['type' => 'custom_notification'];

        if ($request->recipient_type === 'all') {
            $tokens = DB::table('fcm_tokens')->pluck('fcm_token')->unique()->values();
            $recipientIds   = null;
            $recipientCount = $tokens->count();
            $result = ['sent' => 0, 'failed' => 0];

            foreach ($tokens as $token) {
                if ($fcm->sendNotification($token, $title, $body, $data)) {
                    $result['sent']++;
                } else {
                    $result['failed']++;
                }
            }
        } else {
            $userIds = $request->recipient_ids;
            $tokens = DB::table('fcm_tokens')
                ->whereIn('user_id', $userIds)
                ->pluck('fcm_token')
                ->unique()
                ->values();

            $recipientIds   = $userIds;
            $recipientCount = count($userIds);
            $result = ['sent' => 0, 'failed' => 0];

            foreach ($tokens as $token) {
                if ($fcm->sendNotification($token, $title, $body, $data)) {
                    $result['sent']++;
                } else {
                    $result['failed']++;
                }
            }
        }

        // Simpan history
        NotificationHistory::create([
            'type'           => 'custom',
            'title'          => $title,
            'body'           => $body,
            'data'           => $data,
            'sent_by'        => $user->id_user,
            'recipient_type' => $request->recipient_type,
            'recipient_ids'  => $recipientIds,
            'recipient_count'=> $recipientCount,
        ]);

        $msg = "Notifikasi berhasil dikirim ke {$result['sent']} perangkat.";
        if ($result['failed'] > 0) {
            $msg .= " ({$result['failed']} gagal)";
        }

        return redirect()->route('notifikasi.index')->with('success', $msg);
    }
}
