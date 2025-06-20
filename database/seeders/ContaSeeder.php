<?php

namespace Database\Seeders;

use App\Models\Conta;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContaSeeder extends Seeder
{
    public function run(): void
    {
        // Certifique-se de que existam usuários no banco
        if (User::count() === 0) {
            User::factory()->count(5)->create(); // ou crie manualmente
        }

        $users = User::all();

        foreach ($users as $user) {
            Conta::create([
                'user_id' => $user->id,
                'nome' => 'Conta Corrente de ' . $user->name,
                'tipo' => 'conta_corrente',
                'banco' => 'Banco do Teste',
                'numero_conta' => '000' . $user->id,
                'saldo_atual' => rand(1000, 10000) / 100,
                'limite' => 1000,
                'data_fechamento' => rand(1, 28),
                'data_vencimento' => rand(1, 28),
                'ativo' => true,
            ]);

            Conta::create([
                'user_id' => $user->id,
                'nome' => 'Cartão de Crédito de ' . $user->name,
                'tipo' => 'cartao_credito',
                'banco' => 'Cartões XPTO',
                'numero_conta' => '999' . $user->id,
                'saldo_atual' => 0,
                'limite' => 5000,
                'data_fechamento' => 10,
                'data_vencimento' => 20,
                'ativo' => true,
            ]);
        }
    }
}
