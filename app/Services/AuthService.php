<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use OTPHP\TOTP;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use ParagonIE\ConstantTime\Base32;

class AuthService
{
    /**
     * Generate and send a verification OTP via email.
     *
     * @param User $user
     * @param bool $isFirstVerification
     * @return void
     */
    public function sendVerificationCode(User $user, bool $isFirstVerification = false)
    {
        if (!$user->verification_secret) {
            $randomBytes = random_bytes(10); 
            $user->verification_secret = Base32::encodeUnpadded($randomBytes); 
            $user->save();
        }    


        $otp = TOTP::create($user->verification_secret);
        $otp->setPeriod(300); 
        $code = $otp->now();

        $subject = $isFirstVerification ? "Verificación de email e identidad." : "Verificación de identidad.";
        $message = $isFirstVerification 
            ? "Tu código de verificación de correo e identidad es: $code"
            : "Tu código para verificar identidad es: $code";

        Mail::raw($message, function ($mail) use ($user, $subject) {
            $mail->to($user->email)->subject($subject);
        });
    }

    /**
     * Validate the OTP code.
     *
     * @param string $email
     * @param string $code
     * @return bool
     */
    public function validateVerificationCode(string $email, string $code): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->verification_secret) {
            return false; 
        }

        $otp = TOTP::create($user->verification_secret);
        $otp->setPeriod(300); 
        $code = $otp->now();

        return $otp->verify($code, null, 1); 
    }

    /**
     * Confirm verification and authenticate the user.
     *
     * @param User $user
     * @return string
     */
    public function confirmVerification(User $user)
    {
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->verification_secret = null;
            $user->save();
        }

        $token = JWTAuth::fromUser($user);
        return $token;
    }
}
