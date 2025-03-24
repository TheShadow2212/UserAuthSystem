<?php

namespace App\Services;

use App\Models\User;
use OTPHP\TOTP;
use Tymon\JWTAuth\Facades\JWTAuth;
use ParagonIE\ConstantTime\Base32;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;
use Exception;

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

        $subject = $isFirstVerification 
            ? "Verificación de email e identidad." 
            : "Verificación de identidad.";

        $html = $this->generateEmailTemplate($user->name, $code, $isFirstVerification);

        try {
            $response = Resend::emails()->send([
                'from' => 'TuApp <onboarding@resend.dev>',
                'to' => [$user->email],
                'subject' => $subject,
                'html' => $html,
            ]);

            if (!isset($response['id'])) {
                Log::error('Error al enviar correo: ' . json_encode($response));
            }
        } catch (Exception $e) {
            Log::error('Error de envío con Resend: ' . $e->getMessage());
        }
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

    /**
     * Generate the HTML template for the email.
     *
     * @param string $name
     * @param string $code
     * @param bool $isFirstVerification
     * @return string
     */
    private function generateEmailTemplate(string $name, string $code, bool $isFirstVerification): string
    {
        $title = $isFirstVerification 
            ? "Verificación de Email e Identidad" 
            : "Verificación de Identidad";

        $message = $isFirstVerification
            ? "Gracias por registrarte. Para verificar tu correo e identidad, introduce el siguiente código:"
            : "Por razones de seguridad, verifica tu identidad con el siguiente código:";

        return "
        <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #f4f4f4; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #007BFF;'>$title</h2>
                <p>Hola, <strong>$name</strong>,</p>
                <p>$message</p>
                <div style='text-align: center;'>
                    <p style='font-size: 28px; font-weight: bold; color: #28a745;'>$code</p>
                </div>
                <p>Este código es válido por <strong>5 minutos</strong>.</p>
                <p>Si no solicitaste este código, por favor ignora este mensaje.</p>
            </div>
            <div style='text-align: center; font-size: 12px; color: #aaa; margin-top: 10px;'>
                <p>© " . date('Y') . " TuApp. Todos los derechos reservados.</p>
            </div>
        </div>";
    }
}
