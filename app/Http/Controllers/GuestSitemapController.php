<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class GuestSitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('guest.beranda'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('guest.tentang'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('guest.fasilitas'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('guest.harga'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('guest.kontak'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('guest.pendaftaran'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('guest.daftar-sekolah'), 'priority' => '0.9', 'changefreq' => 'monthly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>'.e($url['loc']).'</loc>';
            $xml .= '<changefreq>'.e($url['changefreq']).'</changefreq>';
            $xml .= '<priority>'.e($url['priority']).'</priority>';
            $xml .= '</url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
