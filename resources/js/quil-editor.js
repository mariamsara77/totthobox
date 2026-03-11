
// Quill-কে গ্লোবাললি লোড করার জন্য ডাইনামিক ফাংশন
window.initQuill = async () => {
    // যদি অলরেডি উইন্ডোতে Quill থেকে থাকে, তবে নতুন করে ডাউনলোড করবে না
    if (window.Quill) return window.Quill;

    try {
        // ১. Quill লাইব্রেরি ইমপোর্ট করা (এটি একটি আলাদা JS ফাইল তৈরি করবে)
        const { default: Quill } = await import('quill');
        
        // ২. Quill-এর CSS ইমপোর্ট করা (এটি একটি আলাদা CSS ফাইল তৈরি করবে)
        await import('quill/dist/quill.snow.css');

        // ৩. গ্লোবাললি সেট করা যাতে পরবর্তীতে সরাসরি পাওয়া যায়
        window.Quill = Quill;
        
        return Quill;
    } catch (error) {
        console.error("Quill load হতে সমস্যা হয়েছে:", error);
    }
};