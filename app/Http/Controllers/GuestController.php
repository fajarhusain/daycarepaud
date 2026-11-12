<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Support\GuestCms;
use App\Support\GuestWhatsApp;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function beranda()
    {
        $cms = GuestCms::data();
        $sekolahs = Sekolah::orderBy('name')->get();

        return view('guest.beranda', compact('cms', 'sekolahs'));
    }

    public function tentang()
    {
        $cms = GuestCms::data();

        return view('guest.tentang', compact('cms'));
    }

    public function fasilitas()
    {
        $cms = GuestCms::data();

        return view('guest.fasilitas', compact('cms'));
    }

    public function harga()
    {
        $cms = GuestCms::data();

        return view('guest.harga', compact('cms'));
    }

    public function pendaftaran()
    {
        $cms = GuestCms::data();
        $sekolahs = Sekolah::active()->orderBy('name')->get();

        return view('guest.pendaftaran', compact('cms', 'sekolahs'));
    }

    public function kontak()
    {
        $cms = GuestCms::data();

        return view('guest.kontak', compact('cms'));
    }

    public function kontakSend(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'pesan' => 'required|string|min:10',
        ]);

        return redirect()->away(GuestWhatsApp::url(
            GuestWhatsApp::demoRequest($validated['nama'], $validated['email'], $validated['pesan'])
        ));
    }
}
