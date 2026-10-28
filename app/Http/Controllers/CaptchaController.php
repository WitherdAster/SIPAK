<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    public const SESSION_KEY = 'captcha_code';

    private const KARAKTER = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * Buat gambar captcha baru dan simpan kodenya di session.
     */
    public function image(Request $request): Response
    {
        $kode = $this->buatKode(5);

        $request->session()->put(self::SESSION_KEY, $kode);

        $lebar = 170;
        $tinggi = 58;

        $gambar = imagecreatetruecolor($lebar, $tinggi);

        $latar = imagecolorallocate($gambar, 241, 245, 249);
        imagefilledrectangle($gambar, 0, 0, $lebar, $tinggi, $latar);

        // Garis pengganggu
        for ($i = 0; $i < 6; $i++) {
            $warna = imagecolorallocate($gambar, random_int(150, 200), random_int(160, 210), random_int(170, 220));
            imageline($gambar, random_int(0, $lebar), random_int(0, $tinggi), random_int(0, $lebar), random_int(0, $tinggi), $warna);
        }

        // Titik pengganggu
        for ($i = 0; $i < 150; $i++) {
            $warna = imagecolorallocate($gambar, random_int(120, 190), random_int(130, 200), random_int(140, 210));
            imagesetpixel($gambar, random_int(0, $lebar), random_int(0, $tinggi), $warna);
        }

        $font = resource_path('fonts/DejaVuSans-Bold.ttf');

        if (is_file($font)) {
            $x = 16;

            foreach (str_split($kode) as $karakter) {
                $warna = imagecolorallocate($gambar, random_int(15, 70), random_int(25, 90), random_int(90, 160));
                $ukuran = random_int(22, 27);
                $sudut = random_int(-18, 18);
                $y = random_int(38, 46);

                imagettftext($gambar, $ukuran, $sudut, $x, $y, $warna, $font, $karakter);

                $x += random_int(28, 31);
            }
        } else {
            $warna = imagecolorallocate($gambar, 30, 64, 175);
            imagestring($gambar, 5, 16, 20, $kode, $warna);
        }

        ob_start();
        imagepng($gambar);
        $isi = (string) ob_get_clean();

        imagedestroy($gambar);

        return response($isi, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function buatKode(int $panjang): string
    {
        $kode = '';
        $maks = strlen(self::KARAKTER) - 1;

        for ($i = 0; $i < $panjang; $i++) {
            $kode .= self::KARAKTER[random_int(0, $maks)];
        }

        return $kode;
    }
}
