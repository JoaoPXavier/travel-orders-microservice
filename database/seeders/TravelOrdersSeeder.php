<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TravelOrder;
use Illuminate\Support\Facades\Hash;

class TravelOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        User::where('email', 'like', '%@empresa.com')->delete();
        TravelOrder::truncate();

        // Create 10 test users.
        $users = [];
        
        $users[] = User::create([
            'name' => 'João Silva',
            'email' => 'joao.silva@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Maria Santos', 
            'email' => 'maria.santos@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Pedro Oliveira',
            'email' => 'pedro.oliveira@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Ana Costa',
            'email' => 'ana.costa@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Carlos Lima',
            'email' => 'carlos.lima@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Fernanda Rocha',
            'email' => 'fernanda.rocha@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Ricardo Alves',
            'email' => 'ricardo.alves@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Juliana Martins',
            'email' => 'juliana.martins@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Roberto Souza',
            'email' => 'roberto.souza@empresa.com',
            'password' => Hash::make('senha123'),
            'is_admin' => false
        ]);

        $users[] = User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@empresa.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true
        ]);

        // Destinations for variation
        $destinations = [
            'São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre',
            'Salvador', 'Fortaleza', 'Recife', 'Brasília', 'Florianópolis', 'Manaus', 'Belém'
        ];

        // Create 20 varied travel requests.
        for ($i = 0; $i < 20; $i++) {
            $user = $users[array_rand($users)];
            $destination = $destinations[array_rand($destinations)];
            
            
            $statusWeights = ['solicitado', 'solicitado', 'aprovado', 'aprovado', 'cancelado'];
            $status = $statusWeights[array_rand($statusWeights)];
            
            $departureDate = now()->addDays(rand(1, 90))->format('Y-m-d');
            $returnDate = date('Y-m-d', strtotime($departureDate . ' + ' . rand(2, 10) . ' days'));

            TravelOrder::create([
                'user_id' => $user->id,
                'order_id' => 'TRV' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'applicant_name' => $user->name,
                'destination' => $destination,
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
                'status' => $status
            ]);
        }

    }
}