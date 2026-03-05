<x-layouts.app.header :title="__('Home')" :description="__('Welcome to the home page')"
    :image="asset('images/logo.gif')">

    <div class="max-w-2xl mx-auto space-y-4 p-3">
        {{-- Header Section --}}
        <div class="text-center mb-12">
            <flux:heading size="xl" level="1" class="mb-2">গোপনীয়তা নীতি (Privacy Policy)</flux:heading>
            <flux:subheading>Totthobox - আপনার তথ্যের সুরক্ষা আমাদের অগ্রাধিকার</flux:subheading>
            <div class="mt-4">
                <flux:separator variant="subtle" />
            </div>
        </div>

        {{-- Introduction --}}
        <section class="mb-10">
            <flux:heading level="2" class="mb-4">ভূমিকা</flux:heading>
            <flux:text class="text-lg leading-relaxed">
                **Totthobox**-এ আপনাকে স্বাগতম। আমরা আমাদের ব্যবহারকারীদের গোপনীয়তা রক্ষায় প্রতিশ্রুতিবদ্ধ। এই
                পৃষ্ঠায় আমরা কীভাবে আপনার তথ্য সংগ্রহ, ব্যবহার এবং সুরক্ষা প্রদান করি তা বিস্তারিত আলোচনা করা
                হয়েছে।
                আমাদের সেবা ব্যবহারের মাধ্যমে আপনি এই নীতিমালার সাথে একমত পোষণ করছেন।
            </flux:text>
        </section>

        {{-- Services Information --}}
        <flux:card class="p-6 mb-10 bg-zinc-50 dark:bg-zinc-800/50">
            <flux:heading level="3" class="mb-4 text-primary-600">আমাদের সেবাসমূহ ও তথ্য ব্যবহার</flux:heading>
            <flux:text>
                আমরা আমাদের বিভিন্ন মডিউলের মাধ্যমে তথ্য প্রদান করি:
            </flux:text>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 list-none">
                <li class="flex items-center gap-2">
                    <flux:icon.check-circle variant="micro" class="text-green-500" />
                    <flux:text>বাংলাদেশ ও আন্তর্জাতিক তথ্য ভাণ্ডার</flux:text>
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon.check-circle variant="micro" class="text-green-500" />
                    <flux:text>ইসলামিক ও স্বাস্থ্য বিষয়ক শিক্ষা</flux:text>
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon.check-circle variant="micro" class="text-green-500" />
                    <flux:text>জরুরী সেবা ও হেল্পলাইন নম্বর</flux:text>
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon.check-circle variant="micro" class="text-green-500" />
                    <flux:text>এমসিকিউ পরীক্ষা ও কনভার্টার টুলস</flux:text>
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon.check-circle variant="micro" class="text-green-500" />
                    <flux:text>এক্সেল এক্সপার্ট টিপস ও ডাটা অ্যানালাইসিস</flux:text>
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon.check-circle variant="micro" class="text-green-500" />
                    <flux:text>ক্রয়-বিক্রয় ও শিশুদের শিক্ষা উপকরণ</flux:text>
                </li>
            </ul>
        </flux:card>

        {{-- Data Collection Section --}}
        <div class="space-y-8">
            <section>
                <flux:heading level="2" class="mb-3">১. তথ্য সংগ্রহ</flux:heading>
                <flux:text>
                    আমরা যখন আপনি আমাদের ওয়েবসাইটে এমসিকিউ পরীক্ষায় অংশগ্রহণ করেন বা ক্রয়-বিক্রয় সেকশন ব্যবহার করেন,
                    তখন
                    আপনার নাম বা ইমেলের মতো সাধারণ তথ্য সংগ্রহ করতে পারি। এছাড়া ভিজিটর অভিজ্ঞতা উন্নত করতে আমরা
                    কুকিজ
                    (Cookies) ব্যবহার করি।
                </flux:text>
            </section>

            <section>
                <flux:heading level="2" class="mb-3">২. তথ্যের ব্যবহার</flux:heading>
                <flux:text>
                    সংগৃহীত তথ্যগুলো নিম্নোক্ত উদ্দেশ্যে ব্যবহৃত হয়:
                </flux:text>
                <ul class="list-disc ml-6 mt-2 space-y-1 text-zinc-600 dark:text-zinc-400">
                    <li>সেবার মান উন্নয়ন ও ব্যক্তিগত অভিজ্ঞতা প্রদান।</li>
                    <li>জরুরি প্রয়োজনে যোগাযোগ বা আপডেট জানানো।</li>
                    <li>এমসিকিউ পরীক্ষার ফলাফল সংরক্ষণ (যদি প্রযোজ্য হয়)।</li>
                </ul>
            </section>

            <section>
                <flux:heading level="2" class="mb-3">৩. তথ্যের নিরাপত্তা</flux:heading>
                <flux:text>
                    আমরা আপনার ব্যক্তিগত তথ্যের সর্বোচ্চ নিরাপত্তা নিশ্চিত করি। কোনো অবস্থাতেই আপনার তথ্য তৃতীয় কোনো
                    পক্ষের কাছে বিক্রয় বা হস্তান্তর করা হয় না, যদি না আইনি কোনো বাধ্যবাধকতা থাকে।
                </flux:text>
            </section>

            <section>
                <flux:heading level="2" class="mb-3">৪. তৃতীয় পক্ষের লিঙ্ক</flux:heading>
                <flux:text>
                    আমাদের সাইটে জরুরি সেবা বা বহিঃস্থ কোনো ওয়েবসাইটের লিঙ্ক থাকতে পারে। ঐ সকল সাইটের নিজস্ব
                    গোপনীয়তা
                    নীতি রয়েছে, যার জন্য Totthobox দায়ী থাকবে না।
                </flux:text>
            </section>
        </div>

        <flux:separator class="my-10" />

        {{-- Contact Section --}}
        <div class="bg-zinc-100 dark:bg-zinc-800 p-8 rounded-2xl text-center">
            <flux:heading level="3" class="mb-2">যোগাযোগ</flux:heading>
            <flux:text>
                আমাদের গোপনীয়তা নীতি নিয়ে কোনো প্রশ্ন থাকলে নির্দ্বিধায় আমাদের সাথে যোগাযোগ করুন।
            </flux:text>
            <div class="mt-6">
                <flux:button variant="primary" icon="envelope" href="/contact-us">সাপোর্ট সেন্টারে মেসেজ দিন
                </flux:button>
            </div>
        </div>

        {{-- Footer Date --}}
        <div class="mt-12 text-center text-zinc-500 text-sm">
            সর্বশেষ আপডেট: {{ now()->format('d F, Y') }}
        </div>
    </div>
</x-layouts.app.header>