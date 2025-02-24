<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Middleware\JwtAuthMiddleware;
use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use App\Services\JwtAuthService;
use App\Services\AuthService;

class AuthController extends Controller
{
    /**
     * JWT authentication service.
     *
     * @var JwtAuthService
     */
    protected $jwtAuthService;

    /**
     * JWT authentication middleware constructor.
     *
     * @param JwtAuthService $jwtAuthService JWT authentication service.
     * @param AuthService $authService General authentication service.
     */
    public function __construct(JwtAuthService $jwtAuthService, AuthService $authService)
    {
        $this->jwtAuthService = $jwtAuthService;
        $this->authService = $authService;
    }

    
    /**
     * Display the registration form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showRegisterForm(Request $request)
    {
        $redirect = $this->jwtAuthService->validateAndRedirect($request);
        if ($redirect) {
            return $redirect;
        }

        return view('auth.register');
    }

    /**
     * Display the login form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showLoginForm(Request $request)
    {
        $redirect = $this->jwtAuthService->validateAndRedirect($request);
        if ($redirect) {
            return $redirect;
        }
        
        return view('auth.login');
    }
    
    /**
     * Show the dashboard if the user is authenticated.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        return view('dashboard', compact('user'));
    }
        
    /**
     * Display the verification form.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showVerificationForm(Request $request)
    {

        $attempts = session('verification_attempts', 0);

        if (!$request->session()->has('email')) {
            return redirect()->route('login')->withErrors(['error' => 'Se debe iniciar sesión antes de ser verificado.']);
        }
        
        if ($attempts >= 3) {
            session()->forget('verification_attempts');
            return redirect()->route('login')->withErrors(['error' => 'Has fallado demasiados intentos. Por favor, inicia sesión nuevamente.']);
        }

        return view('auth.verify', ['email' => session('email')]);
    }
    
    /**
     * Handle user registration.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $redirect = $this->jwtAuthService->validateAndRedirect($request);
        if ($redirect) {
            return $redirect;
        }

        $request->validate([
            'g-recaptcha-response' => 'required|captcha', 
        ], [
            'g-recaptcha-response.required' => 'Por favor completa el reCAPTCHA.',
            'g-recaptcha-response.captcha' => 'La validación del reCAPTCHA ha fallado. Intenta nuevamente.',
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser una cadena de texto.',
            'max' => 'El campo :attribute no puede tener más de :max caracteres.',
            'email' => 'El campo :attribute debe ser una dirección de correo electrónico válida.',
            'unique' => 'El campo :attribute ya está en uso.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', 'Se ha registrado exitosamente. Por favor, inicie sesión para continuar.');
    }

    /**
     * Handle user login.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    { 
        $redirect = $this->jwtAuthService->validateAndRedirect($request);
        if ($redirect) {
            return $redirect;
        }

        $request->validate([
            'g-recaptcha-response' => 'required|captcha', 
        ], [
            'g-recaptcha-response.required' => 'Por favor completa el reCAPTCHA.',
            'g-recaptcha-response.captcha' => 'La validación del reCAPTCHA ha fallado. Intenta nuevamente.',
        ]);
        
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
    
        if (!$user || !auth()->attempt($credentials)) {
            return redirect()->route('login')->withErrors(['error' => 'Credenciales invalidas.']);
        }
        
        $isFirstVerification = is_null($user->email_verified_at);
        $this->authService->sendVerificationCode($user, $isFirstVerification);
    
        session()->forget('verification_attempts');
        return redirect()->route('verify')->with('email', $user->email);
    }
    
    /**
     * Verify the code entered by the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'g-recaptcha-response' => 'required|captcha', 
        ], [
            'g-recaptcha-response.required' => 'Por favor completa el reCAPTCHA.',
            'g-recaptcha-response.captcha' => 'La validación del reCAPTCHA ha fallado. Intenta nuevamente.',
        ]);

        $attempts = session('verification_attempts', 0);

        $validator = Validator::make($request->all(), [
            'code' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            session(['verification_attempts' => $attempts + 1]);
            return redirect()->route('verify')->withErrors(['error' => 'El código ingresado excede o carece de números.'])->with('email', $request->email);
        }        

        $user = User::where('email', $request->email)->first();

        if (!$this->authService->validateVerificationCode($request->email, $request->code)) {
            session(['verification_attempts' => $attempts + 1]);
            return redirect()->route('verify')->withErrors(['error' => 'Código incorrecto. Intenta nuevamente.'])->with('email', $request->email);
        }

        $token = $this->authService->confirmVerification($user);

        return redirect()->route('dashboard')->withCookie(cookie('jwt', $token, 60));
    }

    /**
     * Handle user logout by invalidating the JWT token.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $token = $this->jwtAuthService->getTokenFromCookie($request);
        $user = $this->jwtAuthService->validateToken($token);

        if ($user && $token) {
            JWTAuth::invalidate($token);

            return $this->jwtAuthService->handleAuthMessage('La sesión fue cerrada exitosamente.', 'success');
        }

        return $this->jwtAuthService->handleAuthMessage('Ocurrieron errores a la hora de cerrar sesión, vuelva a iniciar sesión si así desea.');
    }

}
