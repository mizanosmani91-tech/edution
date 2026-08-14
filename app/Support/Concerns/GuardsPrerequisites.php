<?php

namespace App\Support\Concerns;

trait GuardsPrerequisites
{
    /**
     * কোনো ফিচার ব্যবহার করার আগে তার নির্ভরতা (dependency) পূরণ হয়েছে
     * কিনা যাচাই করে। না হলে ইউজারকে সংশ্লিষ্ট পেজে পাঠিয়ে দেয় এবং
     * কেন পাঠানো হলো তা বুঝিয়ে একটা ফ্ল্যাশ বার্তা রেখে দেয় — যেটা
     * app লেআউটে (resources/views/components/layouts/app.blade.php)
     * একটা সতর্কতা ব্যানার হিসেবে দেখানো হয়।
     *
     * true ফেরত দিলে বুঝতে হবে শর্ত পূরণ হয়েছে, কাজ চালিয়ে যাওয়া যাবে।
     * false ফেরত দিলে রিডাইরেক্ট হয়ে গেছে — কলারকে সাথে সাথে return
     * করে দিতে হবে, বাকি কোড চালানো যাবে না।
     */
    protected function guardPrerequisite(bool $satisfied, string $routeName, string $message): bool
    {
        if ($satisfied) {
            return true;
        }

        session()->flash('guard_notice', $message);
        $this->redirect(route($routeName));

        return false;
    }
}
