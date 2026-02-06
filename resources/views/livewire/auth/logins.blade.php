<div class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-md mx-auto bg-white rounded-lg shadow-2xl p-8 z-10 transform transition-all">

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">লগইন করুন</h2>
            <p class="text-gray-600">আপনার অ্যাকাউন্টে প্রবেশ করুন</p>
        </div>

        <form>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">ইমেইল</label>
                <input type="email" id="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="আপনার ইমেইল দিন">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">পাসওয়ার্ড</label>
                <input type="password" id="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="আপনার পাসওয়ার্ড দিন">
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition duration-200">
                লগইন
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            অ্যাকাউন্ট নেই? <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">রেজিস্টার
                করুন</a>
        </p>
    </div>
</div>
