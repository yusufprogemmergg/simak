<?php

namespace App\Helpers;

class TerbilangHelper
{
    public static function terbilang($x)
    {
        $x = abs($x);
        $angka = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $hasil = "";

        if ($x < 12) {
            $hasil = " " . $angka[$x];
        } elseif ($x < 20) {
            $hasil = self::terbilang($x - 10) . " Belas";
        } elseif ($x < 100) {
            $hasil = self::terbilang($x / 10) . " Puluh" . self::terbilang($x % 10);
        } elseif ($x < 200) {
            $hasil = " Seratus" . self::terbilang($x - 100);
        } elseif ($x < 1000) {
            $hasil = self::terbilang($x / 100) . " Ratus" . self::terbilang($x % 100);
        } elseif ($x < 2000) {
            $hasil = " Seribu" . self::terbilang($x - 1000);
        } elseif ($x < 1000000) {
            $hasil = self::terbilang($x / 1000) . " Ribu" . self::terbilang($x % 1000);
        } elseif ($x < 1000000000) {
            $hasil = self::terbilang($x / 1000000) . " Juta" . self::terbilang($x % 1000000);
        } elseif ($x < 1000000000000) {
            $hasil = self::terbilang($x / 1000000000) . " Milyar" . self::terbilang(fmod($x, 1000000000));
        } elseif ($x < 1000000000000000) {
            $hasil = self::terbilang($x / 1000000000000) . " Trilyun" . self::terbilang(fmod($x, 1000000000000));
        }

        return $hasil;
    }

    public static function formatRupiah($angka)
    {
        $kata = trim(self::terbilang($angka));
        if ($kata === "") {
            $kata = "Nol";
        }
        return $kata . " Rupiah";
    }
}
