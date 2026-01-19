<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validação dinâmica baseada na escolha do usuário
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'initial_balance' => 'required|numeric|min:0',
        ];

        // Se tem código de família
        if ($request->has_family_code === 'yes') {
            $rules['family_code'] = 'required|string|exists:families,family_code';
        } else {
            // Se está criando nova família
            $rules['family_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        // Se tem código de família, entrar na família existente
        if ($request->has_family_code === 'yes' && $request->family_code) {
            $family = Family::where('family_code', $request->family_code)->first();
        }
        // Se não tem código, criar nova família
        else {
            $family = Family::create([
                'name' => $request->family_name,
                'family_code' => strtoupper(Str::random(8)),
            ]);
        }

        // Criar usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'family_id' => $family->id,
            'initial_balance' => $request->initial_balance,
        ]);

        // Fazer login
        Auth::login($user);

        // Se criou nova família e quer enviar convites
        if ($request->has_family_code !== 'yes' && $request->has('invite_emails')) {
            $this->sendInvites($family, $request->invite_emails);
        }

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', 'Cadastro realizado com sucesso!');
    }


    private function sendInvites(Family $family, $emails)
    {
        if (empty($emails)) {
            return;
        }

        $emailArray = is_array($emails) ? $emails : explode(',', $emails);

        foreach ($emailArray as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Aqui você implementará o envio de email
                // Por enquanto vamos deixar preparado
                Mail::raw(
                    "Você foi convidado para a família {$family->name}. Use o código: {$family->family_code}",
                    function ($message) use ($email) {
                        $message->to($email)
                            ->subject('Convite para Família Budget');
                    }
                );
            }
        }
    }
}