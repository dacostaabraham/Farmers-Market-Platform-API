<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Settings ─────────────────────────────────────────────────
        Setting::insert([
            ['key' => 'credit_interest_rate', 'value' => '30',   'description' => 'Taux d\'intérêt crédit (%)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'commodity_rate_fcfa',  'value' => '1000', 'description' => 'Taux de conversion cacao (FCFA/kg)', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Users ─────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Admin Principal',
            'email'    => 'admin@farmersmarket.ci',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'phone'    => '+22501000001',
        ]);

        $supervisor1 = User::create([
            'name'          => 'Kouassi Superviseur',
            'email'         => 'supervisor@farmersmarket.ci',
            'password'      => Hash::make('password123'),
            'role'          => 'supervisor',
            'supervisor_id' => $admin->id,
            'phone'         => '+22507112233',
        ]);

        $operator1 = User::create([
            'name'          => 'Bamba Opérateur POS',
            'email'         => 'operator@farmersmarket.ci',
            'password'      => Hash::make('password123'),
            'role'          => 'operator',
            'supervisor_id' => $supervisor1->id,
            'phone'         => '+22505224455',
        ]);

        $operator2 = User::create([
            'name'          => 'Diallo Awa',
            'email'         => 'operator2@farmersmarket.ci',
            'password'      => Hash::make('password123'),
            'role'          => 'operator',
            'supervisor_id' => $supervisor1->id,
            'phone'         => '+22507336677',
        ]);

        // ── Categories (2+ levels deep) ───────────────────────────────
        $pesticides = Category::create(['name' => 'Pesticides', 'slug' => 'pesticides', 'level' => 1]);
        $herbicides = Category::create(['name' => 'Herbicides', 'slug' => 'herbicides', 'parent_id' => $pesticides->id, 'level' => 2]);
        $insecticides = Category::create(['name' => 'Insecticides', 'slug' => 'insecticides', 'parent_id' => $pesticides->id, 'level' => 2]);
        $fongicides = Category::create(['name' => 'Fongicides', 'slug' => 'fongicides', 'parent_id' => $pesticides->id, 'level' => 2]);

        $fertilizers = Category::create(['name' => 'Engrais', 'slug' => 'engrais', 'level' => 1]);
        $npk = Category::create(['name' => 'Engrais NPK', 'slug' => 'engrais-npk', 'parent_id' => $fertilizers->id, 'level' => 2]);
        $organic = Category::create(['name' => 'Engrais Organiques', 'slug' => 'engrais-organiques', 'parent_id' => $fertilizers->id, 'level' => 2]);

        $seeds = Category::create(['name' => 'Semences', 'slug' => 'semences', 'level' => 1]);
        $seedsCacao = Category::create(['name' => 'Semences Cacao', 'slug' => 'semences-cacao', 'parent_id' => $seeds->id, 'level' => 2]);
        $seedsMais = Category::create(['name' => 'Semences Maïs', 'slug' => 'semences-mais', 'parent_id' => $seeds->id, 'level' => 2]);

        // ── Products ──────────────────────────────────────────────────
        $products = [
            ['name' => 'Glyphosate 360 SL (1L)',    'category_id' => $herbicides->id,   'price_fcfa' => 5000,  'unit' => 'bouteille', 'description' => 'Herbicide systémique à large spectre'],
            ['name' => 'Atrazine 500 SC (500ml)',    'category_id' => $herbicides->id,   'price_fcfa' => 3500,  'unit' => 'bouteille', 'description' => 'Herbicide sélectif pour maïs'],
            ['name' => 'Lambda-cyhalothrine (1L)',   'category_id' => $insecticides->id, 'price_fcfa' => 8000,  'unit' => 'bouteille', 'description' => 'Insecticide pyréthrinoïde'],
            ['name' => 'Chlorpyrifos 480 EC (1L)',   'category_id' => $insecticides->id, 'price_fcfa' => 6500,  'unit' => 'bouteille', 'description' => 'Insecticide organo-phosphoré'],
            ['name' => 'Mancozèbe 80 WP (1kg)',      'category_id' => $fongicides->id,   'price_fcfa' => 4500,  'unit' => 'sachet',    'description' => 'Fongicide préventif anti-mildiou'],
            ['name' => 'NPK 10-10-10 (50kg)',        'category_id' => $npk->id,          'price_fcfa' => 18000, 'unit' => 'sac',       'description' => 'Engrais complet de fond'],
            ['name' => 'NPK 0-23-19 (50kg)',         'category_id' => $npk->id,          'price_fcfa' => 20000, 'unit' => 'sac',       'description' => 'Engrais phospho-potassique'],
            ['name' => 'Urée 46% (50kg)',            'category_id' => $npk->id,          'price_fcfa' => 22000, 'unit' => 'sac',       'description' => 'Engrais azoté de couverture'],
            ['name' => 'Compost de fiente (25kg)',   'category_id' => $organic->id,      'price_fcfa' => 3000,  'unit' => 'sac',       'description' => 'Amendement organique naturel'],
            ['name' => 'Semences cacao améliorées',  'category_id' => $seedsCacao->id,   'price_fcfa' => 15000, 'unit' => 'lot/100',   'description' => 'Variété haute performance résistante à la maladie'],
            ['name' => 'Semences maïs hybride CMS',  'category_id' => $seedsMais->id,    'price_fcfa' => 7500,  'unit' => 'sachet 1kg','description' => 'Maïs hybride à fort rendement'],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }

        // ── Farmers ───────────────────────────────────────────────────
        $farmers = [
            ['identifier' => 'CI-2024-001', 'firstname' => 'Konan',   'lastname' => 'Yao',      'phone' => '+22507001001', 'village' => 'Bouaké',     'credit_limit_fcfa' => 500000],
            ['identifier' => 'CI-2024-002', 'firstname' => 'Amara',   'lastname' => 'Coulibaly', 'phone' => '+22507001002', 'village' => 'Daloa',      'credit_limit_fcfa' => 300000],
            ['identifier' => 'CI-2024-003', 'firstname' => 'Adjoua',  'lastname' => 'Brou',      'phone' => '+22507001003', 'village' => 'Gagnoa',     'credit_limit_fcfa' => 400000],
            ['identifier' => 'CI-2024-004', 'firstname' => 'Koffi',   'lastname' => 'Assoumou',  'phone' => '+22507001004', 'village' => 'Abengourou', 'credit_limit_fcfa' => 600000],
            ['identifier' => 'CI-2024-005', 'firstname' => 'Mariam',  'lastname' => 'Diomandé',  'phone' => '+22507001005', 'village' => 'Man',        'credit_limit_fcfa' => 250000],
        ];

        $farmerModels = [];
        foreach ($farmers as $f) {
            $farmerModels[] = Farmer::create($f);
        }

        // ── Demo Transactions (cash + credit) ─────────────────────────
        $p1 = Product::where('name', 'like', 'NPK 10-10-10%')->first();
        $p2 = Product::where('name', 'like', 'Glyphosate%')->first();
        $p3 = Product::where('name', 'like', 'Lambda%')->first();

        // Cash transaction — Farmer 1
        $txCash = Transaction::create([
            'reference'            => 'TXN-' . now()->format('Ymd') . '-0001',
            'farmer_id'            => $farmerModels[0]->id,
            'operator_id'          => $operator1->id,
            'subtotal_fcfa'        => $p1->price_fcfa * 2 + $p2->price_fcfa,
            'total_fcfa'           => $p1->price_fcfa * 2 + $p2->price_fcfa,
            'payment_method'       => 'cash',
            'interest_amount_fcfa' => 0,
        ]);
        TransactionItem::create(['transaction_id' => $txCash->id, 'product_id' => $p1->id, 'product_name' => $p1->name, 'unit_price_fcfa' => $p1->price_fcfa, 'quantity' => 2, 'line_total_fcfa' => $p1->price_fcfa * 2]);
        TransactionItem::create(['transaction_id' => $txCash->id, 'product_id' => $p2->id, 'product_name' => $p2->name, 'unit_price_fcfa' => $p2->price_fcfa, 'quantity' => 1, 'line_total_fcfa' => $p2->price_fcfa]);

        // Credit transaction — Farmer 2 (with interest 30%)
        $subtotal = $p1->price_fcfa * 3;
        $interest = (int) round($subtotal * 0.30);
        $total    = $subtotal + $interest;

        $txCredit = Transaction::create([
            'reference'            => 'TXN-' . now()->format('Ymd') . '-0002',
            'farmer_id'            => $farmerModels[1]->id,
            'operator_id'          => $operator1->id,
            'subtotal_fcfa'        => $subtotal,
            'total_fcfa'           => $total,
            'payment_method'       => 'credit',
            'interest_rate'        => 30.00,
            'interest_amount_fcfa' => $interest,
        ]);
        TransactionItem::create(['transaction_id' => $txCredit->id, 'product_id' => $p1->id, 'product_name' => $p1->name, 'unit_price_fcfa' => $p1->price_fcfa, 'quantity' => 3, 'line_total_fcfa' => $subtotal]);

        Debt::create([
            'farmer_id'             => $farmerModels[1]->id,
            'transaction_id'        => $txCredit->id,
            'original_amount_fcfa'  => $total,
            'remaining_amount_fcfa' => $total,
            'status'                => 'open',
        ]);

        // Another credit — Farmer 3 (partially paid already)
        $subtotal2 = $p3->price_fcfa * 2;
        $interest2 = (int) round($subtotal2 * 0.30);
        $total2    = $subtotal2 + $interest2;

        $txCredit2 = Transaction::create([
            'reference'            => 'TXN-' . now()->format('Ymd') . '-0003',
            'farmer_id'            => $farmerModels[2]->id,
            'operator_id'          => $operator2->id,
            'subtotal_fcfa'        => $subtotal2,
            'total_fcfa'           => $total2,
            'payment_method'       => 'credit',
            'interest_rate'        => 30.00,
            'interest_amount_fcfa' => $interest2,
        ]);
        TransactionItem::create(['transaction_id' => $txCredit2->id, 'product_id' => $p3->id, 'product_name' => $p3->name, 'unit_price_fcfa' => $p3->price_fcfa, 'quantity' => 2, 'line_total_fcfa' => $subtotal2]);

        Debt::create([
            'farmer_id'             => $farmerModels[2]->id,
            'transaction_id'        => $txCredit2->id,
            'original_amount_fcfa'  => $total2,
            'remaining_amount_fcfa' => (int) round($total2 * 0.6), // 40% already paid
            'status'                => 'partially_paid',
        ]);

        $this->command->info('✅ Seeding complete! Demo credentials:');
        $this->command->info('   Admin:      admin@farmersmarket.ci / password123');
        $this->command->info('   Supervisor: supervisor@farmersmarket.ci / password123');
        $this->command->info('   Operator:   operator@farmersmarket.ci / password123');
    }
}
