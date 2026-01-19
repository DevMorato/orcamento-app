<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Família Budget</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
            <div>
                <h2 class="text-center text-3xl font-extrabold text-gray-900">
                    Criar Conta
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Gerencie suas finanças familiares
                </p>
            </div>

            <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST" id="registerForm">
                @csrf

                <!-- Dados Pessoais -->
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo</label>
                        <input id="name" name="name" type="text" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            value="{{ old('name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                        <input id="password" name="password" type="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar
                            Senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="initial_balance" class="block text-sm font-medium text-gray-700">Saldo Inicial
                            (R$)</label>
                        <input id="initial_balance" name="initial_balance" type="number" step="0.01" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            value="{{ old('initial_balance', '0.00') }}">
                        @error('initial_balance')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Família -->
                <div class="border-t pt-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Você tem um código de
                            família?</label>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="has_family_code" value="yes" class="form-radio"
                                    onchange="toggleFamilyCode()">
                                <span class="ml-2">Sim, tenho um código</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="radio" name="has_family_code" value="no" class="form-radio" checked
                                    onchange="toggleFamilyCode()">
                                <span class="ml-2">Não, quero criar uma nova família</span>
                            </label>
                        </div>
                    </div>

                    <div id="familyCodeInput" class="hidden">
                        <label for="family_code" class="block text-sm font-medium text-gray-700">Código da
                            Família</label>
                        <input id="family_code" name="family_code" type="text"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Ex: ABC12345">
                        @error('family_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="newFamilyInput">
                        <label for="family_name" class="block text-sm font-medium text-gray-700">Nome da Família</label>
                        <input id="family_name" name="family_name" type="text"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Ex: Família Silva" value="{{ old('family_name') }}">
                        @error('family_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cadastrar
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('filament.admin.auth.login') }}"
                        class="text-sm text-indigo-600 hover:text-indigo-500">
                        Já tem conta? Faça login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleFamilyCode() {
            const hasCode = document.querySelector('input[name="has_family_code"]:checked').value;
            const codeInput = document.getElementById('familyCodeInput');
            const newFamilyInput = document.getElementById('newFamilyInput');
            const familyCodeField = document.getElementById('family_code');
            const familyNameField = document.getElementById('family_name');

            if (hasCode === 'yes') {
                codeInput.classList.remove('hidden');
                newFamilyInput.classList.add('hidden');
                familyCodeField.required = true;
                familyNameField.required = false;
                familyNameField.value = ''; // Limpar o campo
            } else {
                codeInput.classList.add('hidden');
                newFamilyInput.classList.remove('hidden');
                familyCodeField.required = false;
                familyNameField.required = true;
                familyCodeField.value = ''; // Limpar o campo
            }
        }

        // Executar ao carregar a página para garantir estado correto
        document.addEventListener('DOMContentLoaded', function () {
            toggleFamilyCode();
        });
    </script>
</body>

</html>