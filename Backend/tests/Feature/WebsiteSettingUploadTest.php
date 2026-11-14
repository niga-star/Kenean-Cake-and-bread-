<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WebsiteSettingUploadTest extends TestCase
{
    private array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

        Schema::dropAllTables();

        Schema::create('branches', function (Blueprint $table) {
            $table->id('branch_id');
            $table->string('branch_name');
            $table->string('branch_type')->default('Regular');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('role')->default('Customer');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('Active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id('banner_id');
            $table->string('title_en');
            $table->string('title_am')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->string('subtitle_am')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_am')->nullable();
            $table->string('image_url');
            $table->string('link_url')->nullable();
            $table->string('button_text_en')->nullable();
            $table->string('button_text_am')->nullable();
            $table->string('badge_en')->nullable();
            $table->string('badge_am')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('action');
            $table->string('record')->nullable();
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $filePath) {
            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        parent::tearDown();
    }

    private function makeUser(string $role): User
    {
        return User::create([
            'full_name' => $role . ' User',
            'email' => Str::slug($role) . '_' . uniqid() . '@sweetbakes.test',
            'password' => 'password123',
            'role' => $role,
            'status' => 'Active',
        ]);
    }

    public function test_non_super_admin_cannot_access_settings_or_upload_about_photo(): void
    {
        $manager = $this->makeUser('Branch Manager');
        Sanctum::actingAs($manager);

        $this->getJson('/api/admin/settings')->assertStatus(403);

        $file = UploadedFile::fake()->image('about_photo.jpg', 400, 400);
        $this->postJson('/api/admin/settings/upload-image', [
            'image' => $file,
            'slot' => 'about',
        ])->assertStatus(403);
    }

    public function test_super_admin_can_upload_about_photo_and_read_it_in_business_info_and_settings(): void
    {
        $superAdmin = $this->makeUser('Super Admin');
        Sanctum::actingAs($superAdmin);

        $file = UploadedFile::fake()->image('bakery_chef.png', 800, 600);

        $uploadResponse = $this->post('/api/admin/settings/upload-image', [
            'image' => $file,
            'slot' => 'about',
            'setting_key' => 'about_image_url',
        ]);

        $uploadResponse->assertStatus(200)
            ->assertJsonStructure(['message', 'image_url']);

        $uploadedUrl = $uploadResponse->json('image_url');
        $this->assertStringStartsWith('/uploads/settings/about_', $uploadedUrl);

        $diskPath = public_path(ltrim($uploadedUrl, '/'));
        $this->createdFiles[] = $diskPath;
        $this->assertFileExists($diskPath);

        // Verify persisted in GET /api/admin/settings
        $this->getJson('/api/admin/settings')
            ->assertStatus(200)
            ->assertJsonFragment(['about_image_url' => $uploadedUrl]);

        // Verify public homepage & about endpoint exposes uploaded image URL
        $this->getJson('/api/business-info')
            ->assertStatus(200)
            ->assertJsonFragment(['about_image_url' => $uploadedUrl]);

        // Verify POST /api/admin/settings works end-to-end as well
        $this->postJson('/api/admin/settings', [
            'about_image_url' => $uploadedUrl,
            'about_title_en' => 'Updated Bakery Story',
        ])->assertStatus(200);

        $this->getJson('/api/business-info')
            ->assertStatus(200)
            ->assertJson([
                'about_image_url' => $uploadedUrl,
                'about_title_en' => 'Updated Bakery Story',
            ]);
    }
}

