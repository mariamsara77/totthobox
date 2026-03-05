<?php
use Livewire\Volt\Component;
use App\Http\Controllers\Auth\GoogleLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    // রিটার্ন টাইপ mixed করে দেওয়া হলো এরর এড়াতে
    public function render(): mixed
    {
        return view('livewire.auth.google-one-tap'); // ব্লেড ফাইলে লজিক রাখা ভালো
    }

    #[On('google-one-tap-login')]
    public function handleOneTap($token)
    {
        if (Auth::check())
            return;

        $controller = app(GoogleLoginController::class);

        // সেশন স্টোর এরর এড়াতে ম্যানুয়ালি রিকোয়েস্ট তৈরি
        $request = Request::create('/auth/google/one-tap', 'POST', ['token' => $token]);
        $request->setLaravelSession(session());

        $response = $controller->handleOneTapToken($request);
        $data = $response->getData();

        if ($data->success) {
            return redirect()->to($data->redirect);
        }
    }
}; ?>

<div wire:ignore>
    @guest
        <div id="g_id_onload" data-client_id="{{ config('services.google.client_id') }}"
            data-callback="handleCredentialResponse" data-auto_prompt="true" data-itp_support="true">
        </div>
    @endguest
</div>