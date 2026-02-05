<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Listings
            'listings.view', 'listings.create', 'listings.edit', 'listings.delete', 'listings.publish',
            // Contacts
            'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',
            // Leads
            'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.assign',
            // Deals
            'deals.view', 'deals.create', 'deals.edit', 'deals.delete',
            // Tasks
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            // Reports
            'reports.view', 'reports.export',
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Offices
            'offices.view', 'offices.create', 'offices.edit', 'offices.delete',
            // Settings
            'settings.view', 'settings.edit',
            // Integrations
            'integrations.view', 'integrations.manage',
            // AI Features
            'ai.valuation', 'ai.content', 'ai.copilot',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $officeManager = Role::firstOrCreate(['name' => 'office-manager']);
        $officeManager->syncPermissions([
            'listings.view', 'listings.create', 'listings.edit', 'listings.publish',
            'contacts.view', 'contacts.create', 'contacts.edit',
            'leads.view', 'leads.create', 'leads.edit', 'leads.assign',
            'deals.view', 'deals.create', 'deals.edit',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            'reports.view', 'reports.export',
            'users.view', 'users.create', 'users.edit',
            'ai.valuation', 'ai.content', 'ai.copilot',
        ]);

        $agent = Role::firstOrCreate(['name' => 'agent']);
        $agent->syncPermissions([
            'listings.view', 'listings.create', 'listings.edit',
            'contacts.view', 'contacts.create', 'contacts.edit',
            'leads.view', 'leads.create', 'leads.edit',
            'deals.view', 'deals.create', 'deals.edit',
            'tasks.view', 'tasks.create', 'tasks.edit',
            'reports.view',
            'ai.valuation', 'ai.content', 'ai.copilot',
        ]);

        // Create Default Tenant
        $tenant = \Modules\Core\Models\Tenant::firstOrCreate(
            ['subdomain' => 'demo'],
            [
                'name' => 'Demo Emlak',
                'primary_color' => '#0ea5e9',
                'is_active' => true,
                'trial_ends_at' => now()->addDays(14),
                'features' => ['ai', 'mls', 'portals', 'website'],
            ]
        );

        // Create Default Office
        $office = \Modules\Core\Models\Office::firstOrCreate(
            ['name' => 'Merkez Ofis'],
            [
                'tenant_id' => $tenant->id,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'address' => 'Caferağa Mah. Moda Cad. No:1',
                'phone' => '+90 216 123 45 67',
                'email' => 'info@demo-emlak.com',
                'is_active' => true,
                'is_headquarters' => true,
            ]
        );

        // Create Super Admin User
        $superAdminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@recrm.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'office_id' => $office->id,
                'is_active' => true,
            ]
        );
        $superAdminUser->assignRole('super-admin');

        // Create Demo Agent
        $agentUser = \App\Models\User::firstOrCreate(
            ['email' => 'agent@recrm.com'],
            [
                'name' => 'Demo Danışman',
                'password' => Hash::make('password'),
                'office_id' => $office->id,
                'phone' => '+90 532 123 45 67',
                'title' => 'Gayrimenkul Danışmanı',
                'is_active' => true,
            ]
        );
        $agentUser->assignRole('agent');

        // Create Default Pipelines
        $leadPipeline = \Modules\CRM\Models\Pipeline::firstOrCreate(
            ['name' => 'Lead Pipeline', 'type' => 'lead'],
            [
                'office_id' => $office->id,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $leadStages = [
            ['name' => 'Yeni', 'color' => '#6b7280', 'order' => 1, 'probability' => 10],
            ['name' => 'İletişim Kuruldu', 'color' => '#3b82f6', 'order' => 2, 'probability' => 20],
            ['name' => 'Nitelikli', 'color' => '#8b5cf6', 'order' => 3, 'probability' => 40],
            ['name' => 'Gösterim Yapıldı', 'color' => '#f59e0b', 'order' => 4, 'probability' => 60],
            ['name' => 'Teklif Verildi', 'color' => '#10b981', 'order' => 5, 'probability' => 80],
            ['name' => 'Kazanıldı', 'color' => '#22c55e', 'order' => 6, 'probability' => 100, 'is_won_stage' => true],
            ['name' => 'Kaybedildi', 'color' => '#ef4444', 'order' => 7, 'probability' => 0, 'is_lost_stage' => true],
        ];

        foreach ($leadStages as $stage) {
            \Modules\CRM\Models\PipelineStage::firstOrCreate(
                ['pipeline_id' => $leadPipeline->id, 'name' => $stage['name']],
                $stage
            );
        }

        $dealPipeline = \Modules\CRM\Models\Pipeline::firstOrCreate(
            ['name' => 'Satış Pipeline', 'type' => 'deal'],
            [
                'office_id' => $office->id,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $dealStages = [
            ['name' => 'Müzakere', 'color' => '#3b82f6', 'order' => 1, 'probability' => 30],
            ['name' => 'Sözleşme Hazırlık', 'color' => '#8b5cf6', 'order' => 2, 'probability' => 50],
            ['name' => 'Sözleşme İmzalandı', 'color' => '#f59e0b', 'order' => 3, 'probability' => 70],
            ['name' => 'Kapora Alındı', 'color' => '#10b981', 'order' => 4, 'probability' => 90],
            ['name' => 'Tapu Devri', 'color' => '#22c55e', 'order' => 5, 'probability' => 100, 'is_won_stage' => true],
            ['name' => 'İptal', 'color' => '#ef4444', 'order' => 6, 'probability' => 0, 'is_lost_stage' => true],
        ];

        foreach ($dealStages as $stage) {
            \Modules\CRM\Models\PipelineStage::firstOrCreate(
                ['pipeline_id' => $dealPipeline->id, 'name' => $stage['name']],
                $stage
            );
        }

        // Create Sample Listings
        $listings = [
            [
                'title' => ['tr' => 'Kadıköy\'de Deniz Manzaralı 3+1 Daire', 'en' => 'Sea View 3+1 Apartment in Kadikoy'],
                'type' => 'apartment',
                'category' => 'residential',
                'listing_type' => 'sale',
                'price' => 5500000,
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'neighborhood' => 'Caferağa',
                'gross_sqm' => 145,
                'net_sqm' => 130,
                'room_count' => 3,
                'living_room_count' => 1,
                'bathroom_count' => 2,
                'floor_number' => 5,
                'total_floors' => 8,
                'building_age' => 3,
                'heating_type' => 'Doğalgaz Kombi',
                'status' => 'active',
            ],
            [
                'title' => ['tr' => 'Beşiktaş\'ta Lüks Villa', 'en' => 'Luxury Villa in Besiktas'],
                'type' => 'villa',
                'category' => 'residential',
                'listing_type' => 'sale',
                'price' => 25000000,
                'city' => 'İstanbul',
                'district' => 'Beşiktaş',
                'neighborhood' => 'Etiler',
                'gross_sqm' => 450,
                'net_sqm' => 400,
                'room_count' => 6,
                'living_room_count' => 2,
                'bathroom_count' => 4,
                'floor_number' => 1,
                'total_floors' => 3,
                'building_age' => 5,
                'heating_type' => 'Merkezi',
                'status' => 'active',
            ],
            [
                'title' => ['tr' => 'Şişli\'de Kiralık Ofis', 'en' => 'Office for Rent in Sisli'],
                'type' => 'office',
                'category' => 'commercial',
                'listing_type' => 'rent',
                'price' => 45000,
                'city' => 'İstanbul',
                'district' => 'Şişli',
                'neighborhood' => 'Mecidiyeköy',
                'gross_sqm' => 200,
                'net_sqm' => 180,
                'room_count' => 5,
                'living_room_count' => 0,
                'bathroom_count' => 2,
                'floor_number' => 10,
                'total_floors' => 15,
                'building_age' => 2,
                'heating_type' => 'Merkezi',
                'status' => 'active',
            ],
        ];

        foreach ($listings as $listingData) {
            \Modules\RealEstate\Models\Listing::firstOrCreate(
                ['title->tr' => $listingData['title']['tr']],
                array_merge($listingData, [
                    'office_id' => $office->id,
                    'agent_id' => $agentUser->id,
                    'price_currency' => 'TRY',
                    'published_at' => now(),
                ])
            );
        }

        // Create Sample Contacts
        $contacts = [
            [
                'first_name' => 'Ahmet',
                'last_name' => 'Yılmaz',
                'email' => 'ahmet.yilmaz@email.com',
                'phone' => '+90 532 111 22 33',
                'city' => 'İstanbul',
                'source' => 'website',
            ],
            [
                'first_name' => 'Fatma',
                'last_name' => 'Demir',
                'email' => 'fatma.demir@email.com',
                'phone' => '+90 533 222 33 44',
                'city' => 'İstanbul',
                'source' => 'referral',
            ],
            [
                'first_name' => 'Mehmet',
                'last_name' => 'Kaya',
                'email' => 'mehmet.kaya@email.com',
                'phone' => '+90 534 333 44 55',
                'city' => 'Ankara',
                'source' => 'portal',
                'source_detail' => 'sahibinden',
            ],
        ];

        foreach ($contacts as $contactData) {
            \Modules\CRM\Models\Contact::firstOrCreate(
                ['email' => $contactData['email']],
                array_merge($contactData, [
                    'office_id' => $office->id,
                    'assigned_to' => $agentUser->id,
                    'status' => 'active',
                    'kvkk_consent' => true,
                    'kvkk_consent_date' => now(),
                ])
            );
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Login: admin@recrm.com / password');
        $this->command->info('Agent Login: agent@recrm.com / password');
    }
}
