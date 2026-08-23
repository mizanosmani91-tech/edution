<?php

namespace App\Support;

/**
 * BilingualPdfText
 *
 * প্রশ্নপত্রের মতো জায়গায় একই টেক্সটে বাংলা ও আরবি মিশে থাকতে পারে
 * (যেমনঃ "শব্দার্থ লিখঃ استيقظتُ – نَبَّهَنِي")। সমস্যা হলো DomPDF একটা
 * টেক্সট এলিমেন্টে একটাই font-family ব্যবহার করে — glyph-লেভেল অটো
 * ফলব্যাক করে না। তাই বাংলা ফন্ট (notosansbengali) ডিফল্ট রাখলে আরবি
 * অংশটুকু ফাঁকা বাক্স (tofu) হয়ে যাবে, উল্টোটা করলে বাংলা অংশ ভাঙবে।
 *
 * সমাধানঃ টেক্সটের মধ্যে আরবি স্ক্রিপ্টের (Unicode: Arabic, Arabic
 * Supplement, Arabic Extended-A, Presentation Forms) টানা অংশগুলো খুঁজে
 * বের করে আলাদা <span> এ আরবি ফন্ট (notonaskharabic) + dir="rtl" দিয়ে
 * মুড়ে দেওয়া হয়, বাকি অংশ ডিফল্ট (বাংলা) ফন্টেই থেকে যায়।
 */
class BilingualPdfText
{
    private const ARABIC_RANGES = '\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}';

    public static function render(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $pattern = '/(['.self::ARABIC_RANGES.']['.self::ARABIC_RANGES.'\s]*)/u';
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return nl2br(e($text));
        }

        $html = '';
        foreach ($parts as $i => $part) {
            if ($part === '') {
                continue;
            }

            $escaped = nl2br(e($part));

            // preg_split with PREG_SPLIT_DELIM_CAPTURE রাখে non-matching অংশ
            // জোড় (even) ইনডেক্সে, captured (আরবি) অংশ বিজোড় (odd) ইনডেক্সে।
            if ($i % 2 === 1) {
                $html .= '<span style="font-family: notonaskharabic, sans-serif;" dir="rtl">'.$escaped.'</span>';
            } else {
                $html .= $escaped;
            }
        }

        return $html;
    }
}
