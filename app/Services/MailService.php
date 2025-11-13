<?php
namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class MailService
{

    protected static function setMailConfig()
    {
        $settings = Setting::first();

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host'      => 'smtp.gmail.com',
            'port'      => 587,
            'encryption'=> 'tls',
            'username'  => $settings->email_notif ?? 'ristian.rehi@gmail.com',
            'password'  => $settings->password_email ?? 'aeehgvffpoonbkum',
            'timeout'   => null,
            'auth_mode' => null,
        ]);

        Config::set('mail.from', [
            'address' => $settings->email_notif ?? 'default@gmail.com',
            'name'    => 'notifikasi-email',
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
    public static function sendEmailWithTemplate(string $to, string $subject, string $view, array $data = []): bool
    {
        self::setMailConfig();
        try {
            
            $data['subject'] = $subject; // agar bisa dipakai di view
            $html = View::make($view, $data)->render();
            
            Mail::html($html, function ($message) use ($to, $subject) {
                        $message->to($to)
                                ->subject($subject);
                    });
            return true;
        } catch (\Exception $e) {
            print_r($e);die;
            \Log::error("Mail Error: ".$e->getMessage());
            return false;
        }
    }

}
