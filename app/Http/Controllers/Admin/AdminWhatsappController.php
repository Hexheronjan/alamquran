<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsappQueueJob;
use App\Models\WhatsappMessage;
use App\Models\WhatsappSetting;
use App\Models\WhatsappTemplate;
use App\Services\AdminActivityLogger;
use App\Services\NotificationService;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminWhatsappController extends Controller
{
    public function index()
    {
        $connection = WhatsappService::checkConnection();

        $stats = [
            'total'      => WhatsappMessage::count(),
            'sent'       => WhatsappMessage::where('status', 'sent')->count(),
            'pending'    => WhatsappMessage::where('status', 'pending')->count(),
            'processing' => WhatsappMessage::where('status', 'processing')->count(),
            'failed'     => WhatsappMessage::where('status', 'failed')->count(),
            'today'      => WhatsappMessage::whereDate('created_at', today())->count(),
        ];

        $recentQueued = WhatsappMessage::with(['user', 'santri'])
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        $recentLogs = WhatsappMessage::with(['user', 'santri'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $templates = WhatsappTemplate::orderBy('modul')->get();

        $settings = [
            'provider'            => WhatsappService::getActiveProvider(),
            'quiet_hours_enabled' => WhatsappSetting::get('quiet_hours_enabled', '1'),
            'quiet_hours_start'   => WhatsappSetting::get('quiet_hours_start', '21'),
            'quiet_hours_end'     => WhatsappSetting::get('quiet_hours_end', '6'),
            'onesender_api_url'   => WhatsappSetting::get('onesender_api_url', config('services.onesender.api_url', env('ONESENDER_API_URL'))),
            'onesender_api_key'   => WhatsappSetting::get('onesender_api_key', config('services.onesender.api_key', env('ONESENDER_API_KEY'))),
            'spp_reminder_days'   => WhatsappSetting::get('spp_reminder_days', '1,5,8,10'),
        ];

        return view('admin.whatsapp.index', compact('connection', 'stats', 'recentQueued', 'recentLogs', 'templates', 'settings'));
    }

    public function logs(Request $request)
    {
        $query = WhatsappMessage::with(['user', 'santri']);

        if ($request->filled('status')) {
            if ($request->status === 'sent') {
                $query->whereIn('status', ['sent', 'delivered', 'read']);
            } elseif ($request->status === 'pending') {
                $query->whereIn('status', ['pending', 'processing']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('modul')) {
            $query->where('modul', $request->modul);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('recipient_number', 'like', "%{$s}%")
                  ->orWhere('recipient_name', 'like', "%{$s}%")
                  ->orWhere('message_body', 'like', "%{$s}%")
                  ->orWhere('reference_id', 'like', "%{$s}%");
            });
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $moduls = WhatsappMessage::distinct()->pluck('modul')->filter()->values();

        return view('admin.whatsapp.logs', compact('logs', 'moduls'));
    }

    public function sync(Request $request)
    {
        $pages = (int)$request->input('pages', 2);
        $result = WhatsappService::syncFromGateway($pages);

        AdminActivityLogger::log('sync_whatsapp_gateway', "Melakukan sinkronisasi status pesan dari WhatsApp Gateway");

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('warning', $result['message']);
    }

    public function retry($id)
    {
        $msg = WhatsappMessage::findOrFail($id);

        $msg->update([
            'status'        => 'pending',
            'error_message' => null,
            'scheduled_at'  => null,
        ]);

        dispatch(new ProcessWhatsappQueueJob($msg->id));

        AdminActivityLogger::log('retry_whatsapp_message', "Melakukan retry pengiriman pesan WA ID {$msg->id} ke {$msg->recipient_number}", 'WhatsappMessage', $msg->id);

        return back()->with('success', "Pesan ID {$msg->id} berhasil dimasukkan kembali ke antrean queue.");
    }

    public function sendNow($id)
    {
        $msg = WhatsappMessage::findOrFail($id);

        // Eksekusi pengiriman langsung sekarang juga (synchronous)
        (new ProcessWhatsappQueueJob($msg->id))->handle();
        $msg->refresh();

        AdminActivityLogger::log('send_now_whatsapp_message', "Mengirim manual pesan WA ID {$msg->id} ke {$msg->recipient_number}", 'WhatsappMessage', $msg->id);

        if ($msg->status === 'sent') {
            return back()->with('success', "Pesan ID {$msg->id} berhasil dikirim ke WhatsApp {$msg->recipient_number}!");
        }

        return back()->with('warning', "Status pengiriman pesan ID {$msg->id}: {$msg->status}. " . ($msg->error_message ? "Info: {$msg->error_message}" : ''));
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'nomor' => 'required|string',
            'pesan' => 'required|string|max:1000',
        ]);

        $formatted = WhatsappService::formatPhoneNumber($request->nomor);
        if (!$formatted) {
            return back()->with('error', 'Format nomor WhatsApp tidak valid. Gunakan format 08xxx atau 628xxx.');
        }

        $msg = NotificationService::send([
            'recipient_number' => $formatted,
            'recipient_name'   => 'Test Admin',
            'modul'            => 'Test Gateway',
            'message'          => $request->pesan,
            'priority'         => 'urgent',
            'sync'             => true, // langsung kirim sinkron untuk pengetesan
        ]);

        if ($msg && in_array($msg->status, ['pending', 'processing', 'sent'], true)) {
            return back()->with('success', "Pesan test dimasukkan ke antrean WhatsApp untuk {$formatted}.");
        }

        return back()->with('error', "Gagal mengirim pesan test: " . ($msg?->error_message ?? 'Koneksi error'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'whatsapp_provider'   => 'required|in:onesender,fonnte,wablas,log',
            'quiet_hours_enabled' => 'required|in:0,1',
            'quiet_hours_start'   => 'required|integer|min:0|max:23',
            'quiet_hours_end'     => 'required|integer|min:0|max:23',
            'spp_reminder_days'   => 'required|string|max:100',
        ]);

        WhatsappSetting::set('whatsapp_provider', $request->whatsapp_provider);
        WhatsappSetting::set('quiet_hours_enabled', $request->quiet_hours_enabled);
        WhatsappSetting::set('quiet_hours_start', $request->quiet_hours_start);
        WhatsappSetting::set('quiet_hours_end', $request->quiet_hours_end);
        WhatsappSetting::set('spp_reminder_days', $request->spp_reminder_days);

        if ($request->filled('onesender_api_url')) {
            WhatsappSetting::set('onesender_api_url', $request->onesender_api_url);
        }

        if ($request->filled('onesender_api_key')) {
            WhatsappSetting::set('onesender_api_key', $request->onesender_api_key);
        }

        AdminActivityLogger::log('update_whatsapp_settings', "Memperbarui konfigurasi WhatsApp Gateway");

        return back()->with('success', 'Pengaturan WhatsApp Gateway berhasil disimpan.');
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = WhatsappTemplate::findOrFail($id);

        $request->validate([
            'konten'    => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update([
            'konten'    => $request->konten,
            'is_active' => $request->has('is_active'),
        ]);

        AdminActivityLogger::log('update_whatsapp_template', "Memperbarui template WhatsApp: {$template->kode}");

        return back()->with('success', "Template '{$template->nama}' berhasil diperbarui.");
    }
}
