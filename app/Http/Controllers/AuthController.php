<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $apiUrl = 'https://votre-api.com/api';

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $response = Http::post($this->apiUrl . '/login', [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                // Stockage du token dans la session
                Session::put('api_token', $data['token']);
                return redirect()->route('dashboard');
            } else {
                return redirect()->back()->with('error', $data['message'] ?? 'Échec de la connexion');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur de connexion à lAPI');
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $response = Http::post($this->apiUrl . '/register', [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
            ]);

            $data = $response->json();

            if ($response->successful()) {
                // Stockage du token dans la session si l'API le renvoie lors de l'inscription
                if (isset($data['token'])) {
                    Session::put('api_token', $data['token']);
                }
                return redirect()->route('login')->with('success', 'Inscription réussie! Veuillez vous connecter.');
            } else {
                return redirect()->back()->with('error', $data['message'] ?? 'Échec de l inscription');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur de connexion à l API');
        }
    }

    public function logout()
    {
        Session::forget('api_token');
        return redirect()->route('login');
    }
}