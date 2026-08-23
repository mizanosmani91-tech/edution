<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ⚠️ গুরুত্বপূর্ণ ফিক্স: সার্ভার Cloudflare + nginx + Apache এর পেছনে
        // থাকায় Laravel বুঝতে পারে না আসল রিকোয়েস্টটা https ছিল, ফলে
        // redirect()/url() হেল্পার http:// লিংক জেনারেট করছিল। Cloudflare এর
        // "Always Use HTTPS" আবার সেটাকে https এ পাঠাচ্ছিল — এতে অসীম
        // redirect loop (ERR_TOO_MANY_REDIRECTS) তৈরি হচ্ছিল, বিশেষত
        // tenant subdomain গুলোতে (যেমন flourish.edution.xyz)।
        // APP_URL সবসময় https হওয়ায় সব জেনারেটেড URL কে জোর করে https
        // স্কিমে বেঁধে দিচ্ছি।
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // ⚠️ গুরুত্বপূর্ণ ফিক্স: Livewire এর প্রতিটা wire:click/wire:model
        // action আলাদা AJAX request পাঠায় নিজস্ব /livewire/update route এ,
        // যেখানে আমাদের কাস্টম 'tenant.context' middleware ডিফল্টে attach
        // থাকে না। ফলে Livewire component এর কোনো method call করলেই
        // (যেমন modal খোলা, save করা) tenant context হারিয়ে যাচ্ছিল।
        //
        // setUpdateRoute দিয়ে Livewire কে বলে দিচ্ছি এই route টাও যেন
        // 'web' + 'auth' + 'tenant.context' middleware দিয়েই চলে —
        // পেজ লোড রুটের মতোই একই security guarantee সব Livewire action এ।
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web', 'auth', 'tenant.context']);
        });

        $this->registerPdfFonts();
    }

    /**
     * ⚠️ গুরুত্বপূর্ণ ফিক্স: DomPDF-এর বান্ডিল করা ডিফল্ট ফন্ট "DejaVu Sans"-এ
     * বাংলা (এমনকি আরবি) স্ক্রিপ্টের কোনো glyph নেই — তাই এতদিন সব
     * PDF ডকুমেন্টে (এডমিট কার্ড, মার্কশিট, সিট প্ল্যান, সার্টিফিকেট
     * ভেরিফিকেশন ইত্যাদি) প্রতিষ্ঠানের নাম/ছাত্রের নাম সহ যেকোনো বাংলা
     * টেক্সট ফাঁকা বাক্স (tofu) হয়ে প্রিন্ট হচ্ছিল, লাইভে PDF খুলে
     * পরীক্ষা করার পরই এই বাগটা ধরা পড়েছে। এখন Noto Sans Bengali ও
     * Noto Naskh Arabic ফন্ট (resources/fonts) DomPDF-এর FontMetrics-এ
     * রেজিস্টার করে দেওয়া হচ্ছে, যাতে pdf ব্লেড ভিউগুলো
     * font-family: notosansbengali ব্যবহার করে সঠিক বাংলা রেন্ডার করতে পারে।
     *
     * প্রথমবার রেজিস্টার হওয়ার পর storage/fonts এ ক্যাশ হয়ে যায় (VPS-এ
     * ডিপ্লয়ের পরও টিকে থাকে, কারণ storage/ গিট-ট্র্যাকড না), তাই একটা
     * মার্কার ফাইল দিয়ে চেক করে প্রতি রিকোয়েস্টে অকারণে dompdf বুট করা
     * এড়ানো হচ্ছে।
     */
    private function registerPdfFonts(): void
    {
        if (! class_exists(\Dompdf\FontMetrics::class)) {
            return;
        }

        $fontDir = storage_path('fonts');
        if (! is_dir($fontDir)) {
            @mkdir($fontDir, 0755, true);
        }

        // ⚠️ v2: v1 মার্কার আগের একটা ডিপ্লয়ে ভুলভাবে লেখা হয়ে গিয়েছিল
        // (registerFont() ব্যর্থ হলেও রিটার্ন ভ্যালু চেক না করেই মার্কার
        // লিখে ফেলা হচ্ছিল, ফলে ব্যর্থ রেজিস্ট্রেশন স্থায়ীভাবে "সফল" হিসেবে
        // আটকে থাকছিল)। তাই নাম বদলে v2 করা হলো, যাতে পুরনো মার্কার থাকলেও
        // এবার আসলেই রিট্রাই হয়।
                $marker = $fontDir.'/.edution-fonts-registered-v4';
        if (file_exists($marker)) {
            return;
        }

        // ⚠️ FontMetrics-এর কনস্ট্রাক্টর dompdf ভার্সনভেদে ভিন্ন হতে পারে
        // (v3.x এ Canvas + Options দুটোই লাগে) — তাই সরাসরি FontMetrics না
        // বানিয়ে পুরো Dompdf ইনস্ট্যান্স বানিয়ে তার getFontMetrics() ব্যবহার
        // করা হচ্ছে, এটা Canvas ঠিকমতো সেটআপ করে দেয় ভার্সন-নিরপেক্ষভাবে।
                $dompdf = new \Dompdf\Dompdf(['fontDir' => $fontDir, 'fontCache' => $fontDir, 'chroot' => [base_path(), $fontDir, resource_path('fonts')]]);
        $fontMetrics = $dompdf->getFontMetrics();

                $fonts = [
                                ['family' => 'notosansbengali', 'style' => 'normal', 'weight' => 'normal', 'file' => resource_path('fonts/NotoSansBengali-Regular.ttf')],
                                ['family' => 'notosansbengali', 'style' => 'normal', 'weight' => 'bold', 'file' => resource_path('fonts/NotoSansBengali-Regular.ttf')],
                                ['family' => 'notosansbengali', 'style' => 'italic', 'weight' => 'normal', 'file' => resource_path('fonts/NotoSansBengali-Regular.ttf')],
                                ['family' => 'notosansbengali', 'style' => 'italic', 'weight' => 'bold', 'file' => resource_path('fonts/NotoSansBengali-Regular.ttf')],
                                ['family' => 'notonaskharabic', 'style' => 'normal', 'weight' => 'normal', 'file' => resource_path('fonts/NotoNaskhArabic-Regular.ttf')],
                                ['family' => 'notonaskharabic', 'style' => 'normal', 'weight' => 'bold', 'file' => resource_path('fonts/NotoNaskhArabic-Regular.ttf')],
                                ['family' => 'notonaskharabic', 'style' => 'italic', 'weight' => 'normal', 'file' => resource_path('fonts/NotoNaskhArabic-Regular.ttf')],
                                ['family' => 'notonaskharabic', 'style' => 'italic', 'weight' => 'bold', 'file' => resource_path('fonts/NotoNaskhArabic-Regular.ttf')],
                            ];

        $allOk = true;
        foreach ($fonts as $f) {
            if (! file_exists($f['file'])) {
                $allOk = false;
                \Illuminate\Support\Facades\Log::warning("PDF font file missing: {$f['file']}");
                continue;
            }

            $ok = $fontMetrics->registerFont(['family' => $f['family'], 'style' => $f['style'], 'weight' => $f['weight']], $f['file']);
            if (! $ok) {
                $allOk = false;
                \Illuminate\Support\Facades\Log::warning("PDF font registration failed for {$f['family']}");
            }
        }

        // ⚠️ শুধু সব ফন্ট সফলভাবে রেজিস্টার হলেই মার্কার লিখছি — ব্যর্থ হলে
        // পরের রিকোয়েস্টে আবার চেষ্টা হবে (registerFont() নিজেই ইতিমধ্যে
        // সফল ফন্টগুলোর জন্য early-return করে, তাই রিট্রাই সস্তা)।
        // ⚠️ registerFont() শুধুমাত্র এই রিকোয়েস্টের সাময়িক FontMetrics ইনস্ট্যান্সের in-memory এরে সেট করে (setFontFamily), ডিস্কে লেখে না — পরের রিকোয়েস্টে (barryvdh Pdf facade এর আলাদা Dompdf ইনস্ট্যান্স) এই ইন-মেমরি ডেটা হারিয়ে যায় — ফলে এক্সপোর্ট করা PDF-এ কখনও ফন্ট পাওয়া যানি না, বাংলা তাই "?" হয়ে থাকতেসে — এটাই আসল বাগ ছিল।
        // সমাধান: saveFontFamilies() কল করে storage/fonts/installed-fonts.json
        // ফাইলে পার্মানেন্টলি লিখে রাখা হচ্ছে — এই একই ফাইল barryvdh প্যাকেজও পড়ে (সেই ডিফল্ট fontDir সেম ) — তাই যেকোনো পরের Dompdf ইনস্ট্যান্সও এই কাস্টম ফন্ট দেখতে পাবে।
        $fontMetrics->saveFontFamilies();

        if ($allOk) {
            file_put_contents($marker, now()->toDateTimeString());
        }
    }
}
