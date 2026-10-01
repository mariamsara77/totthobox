<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPasswordBase
{
    public function toMail($notifiable)
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $url = $frontendUrl.'/reset-password?token='.$this->token.'&email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('পাসওয়ার্ড রিসেট করুন')
            ->line('আপনার অ্যাকাউন্টের জন্য একটি পাসওয়ার্ড রিসেট রিকোয়েস্ট পাওয়া গেছে।')
            ->action('পাসওয়ার্ড রিসেট করুন', $url)
            ->line('এই লিংকটি ৬০ মিনিটের জন্য কার্যকর থাকবে।')
            ->line('আপনি যদি এই রিকোয়েস্ট না করে থাকেন, তাহলে কোনো পদক্ষেপ নেওয়ার দরকার নেই।');
    }
}