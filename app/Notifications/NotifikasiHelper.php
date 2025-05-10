<?php
namespace App\Notifications;

use Illuminate\Support\Facades\Http;

class NotifikasiHelper
{
    public static function kirimLinkPassword($noHp, $token)
    {
        $link = url("/buat-password/{$token}");
        $pesan = "Halo! Akun koperasi Anda telah dibuat. Silakan buat password Anda di: $link";

        Http::post('https://api-wa-or-sms.com/send', [
            'to' => $noHp,
            'message' => $pesan,
        ]);
    }
}
