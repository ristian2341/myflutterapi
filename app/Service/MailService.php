<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class MailService
{
    protected $settings = [];

    public function __construct()
    {
        $this->settings = Setting::whereIn('key', [
            'mail_from_address',
            'mail_from_name',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption'
        ])->pluck('value', 'key')->toArray();

        config([
            'mail.from.address' => $this->settings['email_notif'] ?? 'ristian.rehi@gmail.com',
            'mail.from.name' => 'notifikasi-email',
            'mail.host' => 'smtp.gmail.com',
            'mail.port' => 587,
            'mail.username' => $this->settings['email_notif'] ?? '',
            'mail.password' => $this->settings['password_email'] ?? '',
            'mail.encryption' => 'tls',
        ]);
    }

    /**
     * Kirim email menggunakan template Blade
     *
     * @param string $to       Alamat penerima
     * @param string $subject  Subjek email
     * @param string $view     Nama view Blade (misal 'emails.test')
     * @param array  $data     Data untuk view
     * @return bool
     */
    public function sendEmailWithTemplate(string $to, string $subject, string $view, array $data = []): bool
    {
        try {
            $data['subject'] = $subject; // agar bisa dipakai di view
            $html = View::make($view, $data)->render();

            Mail::send([], [], function ($message) use ($to, $subject, $html) {
                $message->to($to)
                        ->subject($subject)
                        ->setBody($html, 'text/html');
            });

            return true;
        } catch (\Exception $e) {
            \Log::error("Mail Error: ".$e->getMessage());
            return false;
        }
    }
}
