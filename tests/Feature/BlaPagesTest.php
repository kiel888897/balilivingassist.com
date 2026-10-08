<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CartItem;
use App\Models\DeliveryCoverageArea;
use App\Models\DeliveryRate;
use App\Models\DeliveryVehicle;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPriceTier;
use App\Models\PortfolioImage;
use App\Models\PortfolioProject;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\BlaSeeder;
use Database\Seeders\PortfolioSamplesSeeder;
use Database\Seeders\RentalSamplesSeeder;
use Database\Seeders\SampleProductsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlaPagesTest extends TestCase
{
    public function test_home_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_admin_page_loads(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_authenticates_and_logs_out(): void
    {
        DB::beginTransaction();

        try {
            $password = 'TestAdminPassword123!';
            $user = $this->createUser('admin-login-test@example.test', 'super_admin', $password);

            $this->get(route('login'))->assertStatus(200)->assertSee('Welcome back');
            $this->get(route('admin.login'))->assertStatus(200)->assertSee('Admin sign in');
            $this->post(route('admin.login.store'), [
                'email' => $user->email,
                'password' => $password,
            ])->assertRedirect(route('admin'));

            $this->assertAuthenticatedAs($user);
            $this->get(route('admin'))->assertOk();

            $this->post(route('logout'))->assertRedirect(route('admin.login'));
            $this->assertGuest();
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_catalog_requires_authentication(): void
    {
        $this->get('/admin/products')->assertRedirect(route('admin.login'));
        $this->get('/admin/products/create')->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_sign_in_to_admin(): void
    {
        DB::beginTransaction();

        try {
            $password = 'TestCustomerPassword123!';
            $customer = $this->createUser('customer-login-test@example.test', 'customer', $password);

            $this->post(route('admin.login.store'), [
                'email' => $customer->email,
                'password' => $password,
            ])->assertSessionHasErrors('email');

            $this->assertGuest();
        } finally {
            DB::rollBack();
        }
    }

    public function test_customers_can_register_login_and_logout_separately_from_admins(): void
    {
        DB::beginTransaction();

        try {
            $password = 'NewCustomerPassword2026!';
            $this->get(route('register'))
                ->assertOk()
                ->assertSee('Create your customer account');
            $this->post(route('register.store'), [
                'name' => 'Website Customer',
                'email' => 'website-customer@example.test',
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertRedirect(route('customer.account'));

            $customer = User::where('email', 'website-customer@example.test')->firstOrFail();
            $this->assertTrue($customer->hasRole('customer'));
            $this->assertFalse($customer->hasPermissionTo('admin.access'));
            $this->assertTrue(Hash::check($password, $customer->password));

            $this->get(route('customer.account'))->assertOk()->assertSee('Website Customer');
            $this->post(route('logout'))->assertRedirect(route('login'));

            $this->post(route('login.store'), [
                'email' => $customer->email,
                'password' => $password,
            ])->assertRedirect(route('customer.account'));
            $this->assertAuthenticatedAs($customer);

            $this->get('/admin')->assertForbidden();
        } finally {
            DB::rollBack();
        }
    }

    public function test_public_pages_share_navigation_and_support_indonesian_language(): void
    {
        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('images/bla-logos.png')
            ->assertSee('favicon.png')
            ->assertSee('"@type":"Organization"', false)
            ->assertDontSee('!!json_encode', false)
            ->assertSee('fa-cart-shopping')
            ->assertSee('Portfolio')
            ->assertSeeInOrder([
                'How We Work',
                'Tell us what you need',
                'Site assessment',
                'Quotation & planning',
                'Execution',
                'Handover',
                'We can also support urgent repair requests',
            ])
            ->assertDontSee('Admin login')
            ->assertDontSee('Property Service • Supply • Rental Platform');

        $this->get('/?lang=id')
            ->assertOk()
            ->assertSee('Bagaimana Kami Bekerja')
            ->assertSee('Serah terima');

        $this->get('/services?lang=id')
            ->assertOk()
            ->assertSee('Layanan Kami')
            ->assertSee('Renovasi & Pembangunan');

        $this->get('/language/en?next=%2Fservices%3Flang%3Did')
            ->assertRedirect('/services?lang=en');

        $this->get('/about')->assertRedirect(route('home'));
        $this->get('/projects')->assertRedirect(route('portfolio'));
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:');
    }

    public function test_public_contact_and_social_links_use_business_details(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('mailto:hello@balilivingassist.com', false)
            ->assertSee('https://wa.me/6285173323293', false)
            ->assertSee('+62 851-7332-3293')
            ->assertSee('https://www.instagram.com/balilivingassist/', false)
            ->assertSee('https://www.tiktok.com/@balilivingassist', false)
            ->assertSee('https://www.threads.com/@balilivingassist', false)
            ->assertSee('https://www.facebook.com/share/1M2ia8tVgB/', false)
            ->assertSee('fa-tiktok')
            ->assertSee('fa-threads');
    }

    public function test_admin_routes_are_limited_by_role_permissions(): void
    {
        DB::beginTransaction();

        try {
            $sales = $this->createUser('sales-role-test@example.test', 'sales');
            $this->actingAs($sales)->get('/admin')->assertOk();
            $this->get('/admin/products')->assertForbidden();
            $this->get('/admin/products/create')->assertForbidden();
            $this->get('/admin/categories')->assertForbidden();

            $productManager = $this->createUser('product-role-test@example.test', 'product_manager');
            $this->actingAs($productManager)->get('/admin/products')->assertOk();
            $this->get('/admin/products/create')->assertOk();
            $this->get('/admin/categories')->assertOk();
            $this->get('/admin/users')->assertForbidden();
        } finally {
            DB::rollBack();
        }
    }

    public function test_authenticated_user_without_admin_role_cannot_access_admin(): void
    {
        DB::beginTransaction();

        try {
            $user = $this->createUser('regular-user-test@example.test', 'customer');

            $this->actingAs($user)->get('/admin')->assertForbidden();
        } finally {
            DB::rollBack();
        }
    }

    public function test_product_detail_page_loads(): void
    {
        $response = $this->get('/shop/cement-premium');

        $response->assertStatus(200);
        $response->assertSee('Cement Premium');
    }

    public function test_sample_product_seeder_creates_complete_products_without_duplicates(): void
    {
        DB::beginTransaction();

        try {
            $this->seed(BlaSeeder::class);
            $this->seed(SampleProductsSeeder::class);
            $this->seed(SampleProductsSeeder::class);

            $slugs = [
                'sample-porcelain-wall-floor-tile',
                'sample-smart-wifi-door-lock',
                'sample-teak-outdoor-lounge-chair',
            ];
            $products = Product::with([
                'category',
                'images',
                'attributes.values',
                'variants.values.attribute',
                'variants.priceTiers',
                'frequentlyBoughtTogether',
            ])->whereIn('slug', $slugs)->get();

            $this->assertCount(3, $products);
            $this->assertCount(3, $products->pluck('category_id')->unique());

            foreach ($products as $product) {
                $this->assertCount(2, $product->images);
                $this->assertCount(2, $product->attributes);
                $this->assertCount(4, $product->variants);
                $this->assertCount(2, $product->frequentlyBoughtTogether);

                foreach ($product->variants as $variant) {
                    $this->assertCount(2, $variant->values);
                    $this->assertCount(1, $variant->priceTiers);
                }

                $this->get(route('product.show', $product->slug))
                    ->assertOk()
                    ->assertSee($product->name);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function test_portfolio_sample_seeder_creates_labeled_multi_photo_projects_without_duplicates(): void
    {
        DB::beginTransaction();

        try {
            $this->seed(PortfolioSamplesSeeder::class);
            $this->seed(PortfolioSamplesSeeder::class);

            $projects = PortfolioProject::with('images')
                ->orderBy('sort_order')
                ->get();
            $this->assertCount(3, $projects);
            $this->assertSame([3, 2, 2], $projects->map(function ($project) {
                return $project->images->count();
            })->all());

            foreach ($projects as $project) {
                $this->assertTrue($project->is_sample);
                $this->assertTrue($project->is_active);
                foreach ($project->images as $image) {
                    $this->assertTrue(Storage::disk('public')->exists($image->image_path));
                }
            }

            $this->get(route('portfolio'))
                ->assertOk()
                ->assertSee('AC Service & Maintenance')
                ->assertSee('Villa Interior Renovation')
                ->assertSee('Garden & Poolside Care')
                ->assertSee('Illustrative sample')
                ->assertSee('data-portfolio-slider', false)
                ->assertSee('data-portfolio-step="1"', false)
                ->assertSee('data-portfolio-count', false);
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_create_update_and_delete_portfolio_projects_and_photos(): void
    {
        DB::beginTransaction();
        Storage::fake('public');

        try {
            $admin = $this->createUser('portfolio-admin-test@example.test', 'super_admin');
            $this->actingAs($admin);
            $this->get(route('admin.portfolio'))
                ->assertOk()
                ->assertSee('Portfolio projects');

            $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
            $makeImage = function (string $name) use ($png) {
                return UploadedFile::fake()->createWithContent($name, $png);
            };
            $this->post(route('admin.portfolio.store'), [
                'title' => 'AC Service Gallery Test',
                'description' => 'Example slider description.',
                'is_active' => '1',
                'is_sample' => '1',
                'sort_order' => '5',
                'images' => [
                    $makeImage('ac-before.png'),
                    $makeImage('ac-after.png'),
                ],
            ])->assertSessionHasNoErrors();

            $project = PortfolioProject::where('slug', 'ac-service-gallery-test')->firstOrFail();
            $this->assertDatabaseHas('portfolio_projects', [
                'id' => $project->id,
                'is_sample' => true,
                'is_active' => true,
            ]);
            $this->assertCount(2, $project->images);
            $oldImages = $project->images->keyBy('sort_order');
            $this->assertTrue(Storage::disk('public')->exists($oldImages[0]->image_path));
            $this->get(route('portfolio'))
                ->assertOk()
                ->assertSee('AC Service Gallery Test')
                ->assertSee('Illustrative sample')
                ->assertSee('data-portfolio-slider', false);

            $this->get(route('admin.portfolio.edit', $project))
                ->assertOk()
                ->assertSee('Current photos')
                ->assertSee('Add photos to slider');

            $this->put(route('admin.portfolio.update', $project), [
                'title' => 'Updated AC Service Gallery',
                'description' => 'Updated gallery.',
                'is_active' => '1',
                'is_sample' => '0',
                'sort_order' => '2',
                'gallery' => [
                    $oldImages[0]->id => [
                        'alt_text' => 'Updated AC maintenance photo',
                        'sort_order' => '1',
                    ],
                ],
                'delete_images' => [$oldImages[1]->id],
                'images' => [$makeImage('ac-detail.png')],
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.portfolio.edit', $project));

            $project->refresh()->load('images');
            $this->assertSame('updated-ac-service-gallery', $project->slug);
            $this->assertFalse($project->is_sample);
            $this->assertCount(2, $project->images);
            $this->assertSame('Updated AC maintenance photo', $project->images->first()->alt_text);
            $this->assertFalse(Storage::disk('public')->exists($oldImages[1]->image_path));

            $this->put(route('admin.portfolio.update', $project), [
                'title' => $project->title,
                'description' => $project->description,
                'is_active' => '1',
                'is_sample' => '0',
                'sort_order' => '2',
                'delete_images' => $project->images->pluck('id')->all(),
            ])->assertSessionHasErrors('images');
            $this->assertCount(2, $project->fresh()->images);

            $remainingPaths = $project->images->pluck('image_path')->all();
            $this->delete(route('admin.portfolio.destroy', $project))
                ->assertRedirect(route('admin.portfolio'));
            $this->assertDatabaseMissing('portfolio_projects', ['id' => $project->id]);
            foreach ($remainingPaths as $path) {
                $this->assertFalse(Storage::disk('public')->exists($path));
            }
        } finally {
            DB::rollBack();
        }
    }

    public function test_rental_sample_seeder_creates_complete_products_without_duplicates(): void
    {
        DB::beginTransaction();

        try {
            $this->seed(BlaSeeder::class);
            $this->seed(RentalSamplesSeeder::class);
            $this->seed(RentalSamplesSeeder::class);

            $slugs = [
                'sample-ceramic-cutter',
                'sample-angle-grinder-4-inch',
                'sample-rotary-hammer-drill',
            ];
            $products = Product::with(['category', 'images'])
                ->whereIn('slug', $slugs)
                ->get()
                ->keyBy('slug');

            $this->assertCount(3, $products);
            $this->assertCount(3, $products->pluck('category_id')->unique());

            foreach ($slugs as $slug) {
                $product = $products->get($slug);
                $this->assertNotNull($product);
                $this->assertTrue($product->for_rental);
                $this->assertFalse($product->for_sale);
                $this->assertSame('rental', $product->category->type);
                $this->assertCount(1, $product->images);
                $this->assertGreaterThan(0, $product->rentalPriceForPeriod('daily'));
                $this->assertGreaterThan(0, $product->rentalPriceForPeriod('weekly'));
                $this->assertGreaterThan(0, $product->rentalPriceForPeriod('monthly'));
                $this->assertTrue(Storage::disk('public')->exists($product->image_path));

                $this->get(route('product.show', ['slug' => $slug, 'from' => 'rental']))
                    ->assertOk()
                    ->assertSee($product->name)
                    ->assertSee('data-rental-add-form', false)
                    ->assertSee('data-rental-price="' . number_format($product->rental_price, 0, '.', '') . '"', false)
                    ->assertSee('Weekly (7 days)')
                    ->assertSee('Monthly (28 days)');
            }
        } finally {
            DB::rollBack();
        }
    }

    public function test_product_detail_supports_dynamic_variants_gallery_recommendations_and_session_cart(): void
    {
        DB::beginTransaction();

        try {
            $category = Category::where('type', 'shop')->firstOrFail();
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Dynamic Detail Product',
                'subtitle' => 'Compact edition',
                'slug' => 'dynamic-detail-product',
                'sku' => 'DETAIL-BASE',
                'sale_price' => 100000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'products/detail-second.png',
                'alt_text' => 'Second product view',
                'sort_order' => 2,
            ]);
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'products/detail-main.png',
                'alt_text' => 'Main product view',
                'sort_order' => 1,
            ]);

            $color = ProductAttribute::create([
                'product_id' => $product->id,
                'name' => 'Color',
                'slug' => 'color',
                'input_type' => 'radio',
                'sort_order' => 1,
                'is_active' => true,
            ]);
            $size = ProductAttribute::create([
                'product_id' => $product->id,
                'name' => 'Size',
                'slug' => 'size',
                'input_type' => 'select',
                'sort_order' => 2,
                'is_active' => true,
            ]);
            $black = ProductAttributeValue::create([
                'product_id' => $product->id,
                'product_attribute_id' => $color->id,
                'value' => 'Black',
                'sort_order' => 1,
            ]);
            $gold = ProductAttributeValue::create([
                'product_id' => $product->id,
                'product_attribute_id' => $color->id,
                'value' => 'Gold',
                'sort_order' => 2,
            ]);
            $small = ProductAttributeValue::create([
                'product_id' => $product->id,
                'product_attribute_id' => $size->id,
                'value' => 'Small',
                'sort_order' => 1,
            ]);
            $large = ProductAttributeValue::create([
                'product_id' => $product->id,
                'product_attribute_id' => $size->id,
                'value' => 'Large',
                'sort_order' => 2,
            ]);

            $blackSmall = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'DETAIL-BLACK-SMALL',
                'price' => 100000,
                'stock_quantity' => 10,
                'is_active' => true,
            ]);
            $blackSmall->values()->attach($black->id, [
                'product_id' => $product->id,
                'product_attribute_id' => $color->id,
            ]);
            $blackSmall->values()->attach($small->id, [
                'product_id' => $product->id,
                'product_attribute_id' => $size->id,
            ]);
            $goldLarge = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'DETAIL-GOLD-LARGE',
                'price' => 125000,
                'stock_quantity' => 2,
                'is_active' => true,
            ]);
            $goldLarge->values()->attach($gold->id, [
                'product_id' => $product->id,
                'product_attribute_id' => $color->id,
            ]);
            $goldLarge->values()->attach($large->id, [
                'product_id' => $product->id,
                'product_attribute_id' => $size->id,
            ]);
            ProductVariantPriceTier::create([
                'product_variant_id' => $blackSmall->id,
                'min_quantity' => 3,
                'unit_price' => 90000,
            ]);

            $recommended = Product::create([
                'category_id' => $category->id,
                'name' => 'Recommended Detail Product',
                'slug' => 'recommended-detail-product',
                'sale_price' => 15000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            DB::table('frequently_bought_together')->insert([
                'product_id' => $product->id,
                'related_product_id' => $recommended->id,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->get(route('product.show', $product->slug))
                ->assertOk()
                ->assertSee('Dynamic Detail Product')
                ->assertSee('Compact edition')
                ->assertSee('Color')
                ->assertSee('Size')
                ->assertDontSee('SKU: DETAIL-BASE')
                ->assertDontSee('data-selected-sku', false)
                ->assertSee('data-image-url="http://localhost/storage/products/detail-main.png"', false)
                ->assertSeeInOrder(['detail-main.png', 'detail-second.png'])
                ->assertSee('Frequently bought together')
                ->assertSee('Recommended Detail Product')
                ->assertSee('minQuantity":3', false);

            $customer = $this->createUser('cart-detail-customer@example.test', 'customer');
            $this->actingAs($customer);
            $this->from(route('product.show', $product->slug))
                ->post(route('cart.items.store'), [
                    'product_id' => $product->id,
                    'variant_id' => $blackSmall->id,
                    'quantity' => 3,
                ])
                ->assertRedirect(route('product.show', $product->slug))
                ->assertSessionHas('status');
            $cartItem = $customer->cartItems()->where('product_id', $product->id)->firstOrFail();
            $this->assertSame($blackSmall->id, $cartItem->variant_id);
            $this->assertSame(3, $cartItem->quantity);

            $this->get(route('cart'))
                ->assertOk()
                ->assertDontSee('DETAIL-BLACK-SMALL')
                ->assertSee('Black')
                ->assertSee('Small')
                ->assertSee('Rp 270.000')
                ->assertSee('name="quantity" value="3"', false);

            $this->post(route('cart.items.update'), [
                'cart_item_id' => $cartItem->id,
                'quantity' => 4,
            ])->assertRedirect(route('cart'));

            $this->get(route('cart'))->assertSee('Rp 360.000');

            $this->post(route('cart.items.destroy'), [
                'cart_item_id' => $cartItem->id,
            ])->assertRedirect(route('cart'));

            $this->get(route('cart'))
                ->assertOk()
                ->assertSee('Your cart is empty');
        } finally {
            DB::rollBack();
        }
    }

    public function test_customers_must_sign_in_before_adding_products_and_cart_is_account_scoped(): void
    {
        DB::beginTransaction();

        try {
            $category = Category::where('type', 'shop')->firstOrFail();
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Login Required Cart Product',
                'slug' => 'login-required-cart-product',
                'sale_price' => 45000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            $customer = $this->createUser('cart-login-customer@example.test', 'customer', 'CustomerPass123!');
            $otherCustomer = $this->createUser('other-cart-customer@example.test', 'customer', 'CustomerPass123!');

            $this->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ])->assertRedirect(route('login'))
                ->assertSessionHas('url.intended', route('product.show', $product->slug));
            $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
            $this->get(route('login'))
                ->assertOk()
                ->assertSee('Sign in to your customer account or register first', false);

            $this->post(route('login.store'), [
                'email' => $customer->email,
                'password' => 'CustomerPass123!',
            ])->assertRedirect(route('product.show', $product->slug));
            $this->assertAuthenticatedAs($customer);
            $this->actingAs($customer);

            $this->from(route('product.show', $product->slug))
                ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])->assertRedirect(route('product.show', $product->slug));
            $this->assertDatabaseHas('cart_items', [
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'variant_key' => 0,
                'quantity' => 2,
            ]);

            $this->actingAs($otherCustomer)
                ->get(route('cart'))
                ->assertOk()
                ->assertSee('Your cart is empty');
            $this->post(route('cart.items.destroy'), [
                'cart_item_id' => $customer->cartItems()->firstOrFail()->id,
            ])->assertRedirect(route('cart'));
            $this->assertDatabaseHas('cart_items', [
                'user_id' => $customer->id,
                'product_id' => $product->id,
            ]);

            $this->actingAs($customer)
                ->get(route('cart'))
                ->assertOk()
                ->assertSee('Login Required Cart Product')
                ->assertSee('name="cart_item_id" value="' . $customer->cartItems()->firstOrFail()->id . '"', false);
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_manage_product_detail_gallery_variants_price_tiers_and_recommendations(): void
    {
        DB::beginTransaction();
        Storage::fake('public');

        try {
            $admin = $this->createUser('admin-product-details-test@example.test', 'super_admin');
            $this->actingAs($admin);
            $category = Category::where('type', 'shop')->firstOrFail();
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Admin Detail Product',
                'slug' => 'admin-detail-product',
                'sale_price' => 50000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            $recommended = Product::create([
                'category_id' => $category->id,
                'name' => 'Admin Recommended Product',
                'slug' => 'admin-recommended-product',
                'sale_price' => 25000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);

            $this->put(route('admin.products.attributes.update', $product), [
                'has_attributes' => '1',
                'attributes' => [
                    [
                        'name' => 'Color',
                        'input_type' => 'radio',
                        'sort_order' => 1,
                        'is_active' => '1',
                        'values' => "Black\nWhite",
                    ],
                    [
                        'name' => 'Size',
                        'input_type' => 'select',
                        'sort_order' => 2,
                        'is_active' => '1',
                        'values' => "Small\nLarge",
                    ],
                ],
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.products.edit', $product));

            $color = ProductAttribute::where('product_id', $product->id)->where('slug', 'color')->firstOrFail();
            $size = ProductAttribute::where('product_id', $product->id)->where('slug', 'size')->firstOrFail();
            $black = ProductAttributeValue::where('product_attribute_id', $color->id)->where('value', 'Black')->firstOrFail();
            $small = ProductAttributeValue::where('product_attribute_id', $size->id)->where('value', 'Small')->firstOrFail();

            $this->put(route('admin.products.variants.update', $product), [
                'has_variants' => '1',
                'variants' => [
                    [
                        'sku' => 'ADMIN-DETAIL-BLACK-SMALL',
                        'price' => '50000',
                        'stock_quantity' => '12',
                        'is_active' => '1',
                        'values' => [
                            $color->id => $black->id,
                            $size->id => $small->id,
                        ],
                        'price_tiers' => [
                            ['min_quantity' => '3', 'unit_price' => '45000'],
                        ],
                    ],
                ],
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.products.edit', $product));

            $variant = ProductVariant::where('sku', 'ADMIN-DETAIL-BLACK-SMALL')->firstOrFail();
            $this->assertSame(12, $variant->stock_quantity);
            $this->assertSame(2, $variant->values()->count());
            $this->assertDatabaseHas('product_variant_price_tiers', [
                'product_variant_id' => $variant->id,
                'min_quantity' => 3,
                'unit_price' => '45000.00',
            ]);

            $this->put(route('admin.products.attributes.update', $product), [
                'has_attributes' => '1',
                'attributes' => [
                    [
                        'id' => $color->id,
                        'name' => 'Color',
                        'input_type' => 'radio',
                        'sort_order' => 1,
                        'is_active' => '1',
                        'values' => 'White',
                    ],
                    [
                        'id' => $size->id,
                        'name' => 'Size',
                        'input_type' => 'select',
                        'sort_order' => 2,
                        'is_active' => '1',
                        'values' => "Small\nLarge",
                    ],
                ],
            ])->assertSessionHasErrors('attributes');
            $this->assertDatabaseHas('product_attribute_values', [
                'product_attribute_id' => $color->id,
                'value' => 'Black',
            ]);

            $this->put(route('admin.products.recommendations.update', $product), [
                'recommendation_form_submitted' => '1',
                'related_products' => [$recommended->id],
                'sort_order' => [$recommended->id => 1],
            ])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('frequently_bought_together', [
                'product_id' => $product->id,
                'related_product_id' => $recommended->id,
                'sort_order' => 1,
                'is_active' => 1,
            ]);

            $this->put(route('admin.products.gallery.update', $product), [
                'images' => [UploadedFile::fake()->createWithContent(
                    'admin-gallery.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                )],
            ])->assertSessionHasNoErrors();
            $galleryImage = $product->images()->firstOrFail();
            Storage::disk('public')->assertExists($galleryImage->image_path);

            $this->put(route('admin.products.gallery.update', $product), [
                'gallery' => [
                    $galleryImage->id => [
                        'sort_order' => 4,
                        'alt_text' => 'Sorted gallery image',
                    ],
                ],
            ])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('product_images', [
                'id' => $galleryImage->id,
                'sort_order' => 4,
                'alt_text' => 'Sorted gallery image',
            ]);

            $this->get(route('admin.products.edit', $product))
                ->assertOk()
                ->assertSee('Sorted gallery image')
                ->assertSee('ADMIN-DETAIL-BLACK-SMALL')
                ->assertSee('Admin Recommended Product');
            $this->get(route('product.show', $product->slug))
                ->assertOk()
                ->assertSee('Admin Recommended Product')
                ->assertSee('ADMIN-DETAIL-BLACK-SMALL');

            $this->put(route('admin.products.recommendations.update', $product), [
                'recommendation_form_submitted' => '1',
            ])->assertSessionHasNoErrors();
            $this->assertDatabaseMissing('frequently_bought_together', [
                'product_id' => $product->id,
                'related_product_id' => $recommended->id,
            ]);

            $this->put(route('admin.products.gallery.update', $product), [
                'delete_images' => [$galleryImage->id],
            ])->assertSessionHasNoErrors();
            Storage::disk('public')->assertMissing($galleryImage->image_path);
            $this->assertDatabaseMissing('product_images', ['id' => $galleryImage->id]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_shop_can_filter_search_sort_and_paginate_products(): void
    {
        DB::beginTransaction();

        try {
            $category = Category::create([
                'name' => 'Shop Filter Test',
                'slug' => 'shop-filter-test',
                'type' => 'shop',
                'description' => 'Shop filter test category.',
                'is_active' => true,
            ]);
            Product::create([
                'category_id' => $category->id,
                'name' => 'Filter Test Alpha',
                'slug' => 'filter-test-alpha',
                'sku' => 'FILTER-ALPHA',
                'sale_price' => 10000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            Product::create([
                'category_id' => $category->id,
                'name' => 'Filter Test Zulu',
                'slug' => 'filter-test-zulu',
                'sku' => 'FILTER-ZULU',
                'sale_price' => 20000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);

            $this->get('/shop?category=shop-filter-test&sort=name-desc')
                ->assertOk()
                ->assertSee('Filter Test Alpha')
                ->assertSee('Filter Test Zulu')
                ->assertSee('action="http://localhost/cart/items"', false)
                ->assertSee('name="quantity" value="1"', false)
                ->assertSee('https://wa.me/6285173323293?text=Hi%20Bali%20Living%20Assist%2C%20I%27m%20interested%20in%20Filter%20Test%20Alpha', false)
                ->assertSeeInOrder(['Filter Test Zulu', 'Filter Test Alpha'])
                ->assertDontSee('Cement Premium');
            $this->get('/shop?category=shop-filter-test&q=Alpha')
                ->assertOk()
                ->assertSee('Filter Test Alpha')
                ->assertDontSee('Filter Test Zulu');
        } finally {
            DB::rollBack();
        }
    }

    public function test_rental_can_filter_search_sort_and_paginate_products(): void
    {
        DB::beginTransaction();

        try {
            $category = Category::create([
                'name' => 'Rental Filter Test',
                'slug' => 'rental-filter-test',
                'type' => 'rental',
                'description' => 'Rental filter test category.',
                'is_active' => true,
            ]);

            for ($number = 1; $number <= 13; $number++) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => sprintf('Rental Catalog Test %02d', $number),
                    'slug' => sprintf('rental-catalog-test-%02d', $number),
                    'sku' => sprintf('RENTAL-TEST-%02d', $number),
                    'specifications' => $number === 7 ? "Power output | 1.8 kW\nEngine type | 2-stroke" : null,
                    'sale_price' => $number === 7 ? 4500000 : null,
                    'rental_price' => (14 - $number) * 1000,
                    'for_sale' => $number === 7,
                    'for_rental' => true,
                    'is_active' => true,
                ]);
            }

            $this->get('/rental?category=rental-filter-test')
                ->assertOk()
                ->assertSee('Rental Catalog Test 01')
                ->assertSee('fa-whatsapp', false)
                ->assertSee('page=2', false)
                ->assertDontSee('Cement Premium');

            $this->get('/rental?category=rental-filter-test&sort=price-asc')
                ->assertOk()
                ->assertSeeInOrder(['Rental Catalog Test 13', 'Rental Catalog Test 12']);

            $this->get('/rental?category=rental-filter-test&q=RENTAL-TEST-07')
                ->assertOk()
                ->assertSee('Rental Catalog Test 07')
                ->assertDontSee('Rental Catalog Test 06');

            $this->get(route('product.show', [
                'slug' => 'rental-catalog-test-07',
                'from' => 'rental',
            ]))
                ->assertOk()
                ->assertSee('All products')
                ->assertSee('Rental period')
                ->assertSee('Daily (1 day)')
                ->assertSee('Weekly (7 days)')
                ->assertSee('Monthly (28 days)')
                ->assertSee('data-rental-whatsapp', false)
                ->assertSee('Power output')
                ->assertSee('Specifications')
                ->assertSee('Description')
                ->assertSee('Buy now')
                ->assertSee('Add to cart')
                ->assertDontSee('fa-heart', false);
        } finally {
            DB::rollBack();
        }
    }

    public function test_customer_can_add_rental_periods_to_cart_and_submit_accurate_quote(): void
    {
        DB::beginTransaction();

        try {
            $category = Category::create([
                'name' => 'Rental Cart Test',
                'slug' => 'rental-cart-test',
                'type' => 'rental',
                'description' => 'Rental cart test category.',
                'is_active' => true,
            ]);
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Rental Cart Product',
                'slug' => 'rental-cart-product',
                'sku' => 'RENTAL-CART-001',
                'rental_price' => 60000,
                'weekly_rental_price' => 300000,
                'monthly_rental_price' => 900000,
                'for_sale' => false,
                'for_rental' => true,
                'is_active' => true,
            ]);
            $customer = $this->createUser('rental-cart-test@example.test', 'customer');
            $this->actingAs($customer);
            $rentalUrl = route('product.show', ['slug' => $product->slug, 'from' => 'rental']);

            $this->get($rentalUrl)
                ->assertOk()
                ->assertSee('data-rental-add-form', false)
                ->assertSee('data-rental-price="60000"', false)
                ->assertSee('data-rental-price="300000"', false)
                ->assertSee('data-rental-price="900000"', false)
                ->assertSee('data-rental-subtotal', false)
                ->assertSee('fa-cart-shopping', false);

            $this->from($rentalUrl)->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'rental_period' => 'weekly',
                'quantity' => 2,
            ])->assertSessionHasNoErrors();
            $this->from($rentalUrl)->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'rental_period' => 'daily',
                'quantity' => 1,
            ])->assertSessionHasNoErrors();
            $this->from($rentalUrl)->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'rental_period' => 'monthly',
                'quantity' => 1,
            ])->assertSessionHasNoErrors();

            $weeklyCartItem = CartItem::where('user_id', $customer->id)
                ->where('product_id', $product->id)
                ->where('rental_period', 'weekly')
                ->firstOrFail();
            $this->assertSame(2, $weeklyCartItem->quantity);
            $this->assertSame(3, CartItem::where('user_id', $customer->id)->where('product_id', $product->id)->count());

            $this->postJson(route('cart.items.update'), [
                'cart_item_id' => $weeklyCartItem->id,
                'quantity' => 3,
            ])->assertOk()
                ->assertJsonPath('unit_price', 300000)
                ->assertJsonPath('line_total', 900000)
                ->assertJsonPath('selected_subtotal', 1860000)
                ->assertJsonPath('selected_item_count', 3);

            $this->get(route('cart'))
                ->assertOk()
                ->assertSee('Daily (1 day)')
                ->assertSee('Weekly (7 days)')
                ->assertSee('Monthly (28 days)')
                ->assertSee('1.860.000');
            $this->get(route('customer.checkout'))
                ->assertOk()
                ->assertSee('1.860.000')
                ->assertSee('Weekly (7 days)');

            $this->post(route('customer.quotations.store'), [
                'customer_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Ubud, Bali',
                'customer_note' => 'Rental quote test.',
            ])->assertSessionHasNoErrors();

            $weeklyQuoteItem = QuotationItem::where('item_type', 'rental')
                ->where('product_id', $product->id)
                ->get()
                ->first(function ($item) {
                    return collect($item->options_snapshot ?? [])->contains(function ($option) {
                        return ($option['attribute'] ?? null) === 'Rental period'
                            && ($option['value'] ?? null) === 'weekly';
                    });
                });
            $this->assertNotNull($weeklyQuoteItem);
            $this->assertSame(3, $weeklyQuoteItem->quantity);
            $this->assertEquals(300000, $weeklyQuoteItem->unit_price);
            $this->assertEquals(900000, $weeklyQuoteItem->line_total);
            $this->assertSame([
                ['attribute' => 'Rental period', 'value' => 'weekly'],
            ], $weeklyQuoteItem->options_snapshot);
            $this->assertDatabaseHas('quotations', [
                'user_id' => $customer->id,
                'subtotal' => 1860000,
                'total' => 1860000,
            ]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_products_page_loads(): void
    {
        DB::beginTransaction();

        try {
            $admin = $this->createUser('admin-products-test@example.test', 'super_admin');
            $this->actingAs($admin)
                ->get('/admin/products')
                ->assertOk()
                ->assertSee('Product catalog')
                ->assertSee('data-sidebar-toggle', false)
                ->assertSee('aria-label="Minimize sidebar"', false)
                ->assertSee('overflow-y: auto;', false)
                ->assertSee('id="admin-sidebar-navigation"', false);

            $this->get('/admin/products/create')
                ->assertOk()
                ->assertSee('Add product')
                ->assertSee('Subtitle (optional)')
                ->assertSee('Specifications')
                ->assertSee('Additional product images');
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_create_update_and_delete_a_product(): void
    {
        DB::beginTransaction();
        Storage::fake('public');

        try {
            $admin = $this->createUser('admin-crud-test@example.test', 'super_admin');
            $this->actingAs($admin);

            $category = Category::firstOrFail();
            $productData = [
                'category_id' => $category->id,
                'name' => 'CRUD Test Product',
                'subtitle' => 'Optional product subtitle',
                'sku' => 'CRUD-TEST',
                'description' => '<p><strong>Created</strong> <a href="javascript:alert(2)" onclick="alert(3)">by the catalog test</a>.</p><script>alert(1)</script>',
                'specifications' => "Engine output | 1.8 kW\nBar length | 16 inch",
                'image' => UploadedFile::fake()->createWithContent(
                    'product.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
                'sale_price' => '125000',
                'rental_price' => '60000',
                'weekly_rental_price' => '300000',
                'monthly_rental_price' => '900000',
                'for_sale' => '1',
                'for_rental' => '1',
                'is_active' => '1',
            ];

            $this->post(route('admin.products.store'), array_merge($productData, [
                'images' => [UploadedFile::fake()->createWithContent(
                    'additional-product.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                )],
            ]))
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.products.edit', Product::where('slug', 'crud-test-product')->first()));

            $this->assertDatabaseHas('products', [
                'name' => 'CRUD Test Product',
                'subtitle' => 'Optional product subtitle',
                'specifications' => "Engine output | 1.8 kW\nBar length | 16 inch",
            ]);

            $product = Product::where('slug', 'crud-test-product')->firstOrFail();
            $this->assertSame('125000.00', $product->sale_price);
            $this->assertSame('60000.00', $product->rental_price);
            $this->assertSame('300000.00', $product->weekly_rental_price);
            $this->assertSame('900000.00', $product->monthly_rental_price);
            Storage::disk('public')->assertExists($product->image_path);
            $this->assertStringContainsString('<strong>Created</strong>', $product->description);
            $this->assertStringNotContainsString('<script', $product->description);
            $this->assertStringNotContainsString('alert(1)', $product->description);
            $this->assertStringNotContainsString('javascript:', $product->description);
            $this->assertStringNotContainsString('onclick', $product->description);

            $this->put(route('admin.products.gallery.update', $product), [
                'gallery' => [
                    $product->images()->firstOrFail()->id => [
                        'sort_order' => 0,
                        'alt_text' => 'Main CRUD image',
                    ],
                ],
                'images' => [UploadedFile::fake()->createWithContent(
                    'additional-product.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                )],
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.products.edit', $product));

            $galleryImage = $product->images()->where('image_path', '!=', $product->image_path)->firstOrFail();
            $galleryImagePath = $galleryImage->image_path;
            Storage::disk('public')->assertExists($galleryImage->image_path);
            $this->get(route('admin.products.edit', $product))
                ->assertStatus(200)
                ->assertSee('Edit product')
                ->assertSee('trix-editor')
                ->assertSee('Attributes and options')
                ->assertSee('Variants, stock, and quantity pricing')
                ->assertSee('Weekly rental price (Rp)')
                ->assertSee('Monthly rental price (Rp)')
                ->assertSee('Frequently bought together');
            $oldImagePath = $product->image_path;
            $this->get(route('product.show', $product->slug))
                ->assertOk()
                ->assertSee('<strong>Created</strong>', false)
                ->assertDontSee('alert(1)', false);

            $this->put(route('admin.products.update', $product), array_merge($productData, [
                'name' => 'Updated CRUD Product',
                'sale_price' => '175000',
                'image' => UploadedFile::fake()->createWithContent(
                    'updated-product.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
            ]))->assertRedirect(route('admin.products'));

            $product->refresh();
            Storage::disk('public')->assertMissing($oldImagePath);
            Storage::disk('public')->assertExists($product->image_path);

            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'name' => 'Updated CRUD Product',
                'slug' => 'updated-crud-product',
            ]);

            $this->delete(route('admin.products.destroy', $product))
                ->assertRedirect(route('admin.products'));

            Storage::disk('public')->assertMissing($product->image_path);
            Storage::disk('public')->assertMissing($galleryImagePath);
            $this->assertDatabaseMissing('products', ['id' => $product->id]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_manage_categories_without_deleting_categories_in_use(): void
    {
        DB::beginTransaction();

        try {
            $admin = $this->createUser('admin-categories-test@example.test', 'super_admin');
            $this->actingAs($admin)
                ->get('/admin/categories')
                ->assertOk()
                ->assertSee('Shop category management');

            $categoryData = [
                'name' => 'CRUD Test Category',
                'type' => 'shop',
                'description' => 'Category created in a feature test.',
                'is_active' => '1',
            ];

            $this->post(route('admin.categories.store'), $categoryData)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.categories'));

            $category = Category::where('slug', 'crud-test-category')->firstOrFail();
            $this->get(route('admin.categories.edit', $category))
                ->assertOk()
                ->assertSee('Edit shop category');

            $this->put(route('admin.categories.update', $category), array_merge($categoryData, [
                'name' => 'Updated CRUD Category',
            ]))->assertRedirect(route('admin.categories'));

            $this->assertDatabaseHas('categories', [
                'id' => $category->id,
                'name' => 'Updated CRUD Category',
                'slug' => 'updated-crud-category',
            ]);

            $this->delete(route('admin.categories.destroy', $category))
                ->assertRedirect(route('admin.categories'));
            $this->assertDatabaseMissing('categories', ['id' => $category->id]);

            $categoryInUse = Category::has('products')->firstOrFail();
            $this->delete(route('admin.categories.destroy', $categoryInUse))
                ->assertSessionHas('error');
            $this->assertDatabaseHas('categories', ['id' => $categoryInUse->id]);
            $this->assertDatabaseHas('products', ['category_id' => $categoryInUse->id]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_services_use_fixed_areas_without_categories_and_remain_manageable(): void
    {
        DB::beginTransaction();

        try {
            $projectManager = $this->createUser('service-manager-test@example.test', 'project_manager');
            $this->actingAs($projectManager)
                ->get(route('admin.services'))
                ->assertOk()
                ->assertSee('Service catalog');
            $this->get('/admin/service-categories')->assertNotFound();
            $this->get(route('admin.services.create'))
                ->assertOk()
                ->assertSee('Service area')
                ->assertDontSee('category_id');
            $this->get(route('admin.rental-categories'))->assertForbidden();

            $serviceData = [
                'service_area' => 'service_maintenance',
                'name' => 'Service Workflow Test Item',
                'is_active' => '1',
            ];

            $this->post(route('admin.services.store'), $serviceData)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.services'));

            $service = Service::where('slug', 'service-workflow-test-item')->firstOrFail();
            $this->assertSame('service_maintenance', $service->service_area);

            $this->get(route('services'))
                ->assertOk()
                ->assertSee('Renovation & Building')
                ->assertSee('Service & Maintenance')
                ->assertSee('Procurement & Supply')
                ->assertSeeInOrder([
                    'General renovation & refurbishment',
                    'Painting, finishing & surface works',
                    'Carpentry and installation',
                    'Plumbing, electrical & MEP coordination',
                    'Small construction & improvement works',
                    'Air Conditioner Repair',
                ])
                ->assertSee('Service Workflow Test Item');
            $this->get(route('home'))
                ->assertOk()
                ->assertSee('Service Workflow Test Item');
            $this->get(route('service.show', $service->slug))
                ->assertOk()
                ->assertSee('Service &amp; Maintenance', false)
                ->assertSee('Service Workflow Test Item');

            $this->put(route('admin.services.update', $service), array_merge($serviceData, [
                'name' => 'Updated Service Workflow Item',
                'service_area' => 'procurement_supply',
            ]))->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.services'));

            $this->assertDatabaseHas('services', [
                'id' => $service->id,
                'name' => 'Updated Service Workflow Item',
                'service_area' => 'procurement_supply',
            ]);

            $this->delete(route('admin.services.destroy', $service))
                ->assertRedirect(route('admin.services'));
            $this->assertDatabaseMissing('services', ['id' => $service->id]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_rental_categories_are_separately_managed_and_rendered_publicly(): void
    {
        DB::beginTransaction();

        try {
            $rentalManager = $this->createUser('rental-manager-test@example.test', 'rental_manager');
            $this->actingAs($rentalManager)
                ->get(route('admin.rental-categories'))
                ->assertOk()
                ->assertSee('Rental category management');
            $this->get(route('admin.services'))->assertForbidden();

            $this->post(route('admin.rental-categories.store'), [
                'name' => 'Air Tools Test',
                'type' => 'rental',
                'description' => 'Rental category test.',
                'is_active' => '1',
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.rental-categories'));

            $category = Category::where('slug', 'air-tools-test')->firstOrFail();
            $this->get(route('rental'))
                ->assertOk()
                ->assertSee('Air Tools Test');
            $this->get(route('home'))
                ->assertOk()
                ->assertSee('Air Tools Test');

            $productManager = $this->createUser('rental-category-denied-test@example.test', 'product_manager');
            $this->actingAs($productManager)
                ->get(route('admin.rental-categories'))
                ->assertForbidden();
        } finally {
            DB::rollBack();
        }
    }

    public function test_category_image_upload_is_saved_and_rendered_on_homepage(): void
    {
        DB::beginTransaction();
        Storage::fake('public');

        try {
            $admin = $this->createUser('admin-category-image-test@example.test', 'super_admin');
            $this->actingAs($admin);
            $imageContents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

            $this->post(route('admin.categories.store'), [
                'name' => 'Image Test Category',
                'type' => 'shop',
                'description' => 'Homepage image test.',
                'is_active' => '1',
                'image' => UploadedFile::fake()->createWithContent('category.png', $imageContents),
            ])->assertSessionHasNoErrors();

            $category = Category::where('slug', 'image-test-category')->firstOrFail();
            $this->assertNotEmpty($category->image_path);
            Storage::disk('public')->assertExists($category->image_path);

            $this->get('/')
                ->assertOk()
                ->assertSee('storage/' . $category->image_path);
        } finally {
            DB::rollBack();
        }
    }

    public function test_super_admin_can_manage_users_but_other_roles_cannot(): void
    {
        DB::beginTransaction();

        try {
            $admin = $this->createUser('admin-user-management-test@example.test', 'super_admin');
            $this->actingAs($admin)
                ->get('/admin/users')
                ->assertOk()
                ->assertSee('User management');

            $customerRole = Role::where('slug', 'customer')->firstOrFail();
            $userData = [
                'name' => 'Managed Test User',
                'email' => 'managed-test-user@example.test',
                'role_id' => $customerRole->id,
                'password' => 'ManagedUserPassword123!',
                'password_confirmation' => 'ManagedUserPassword123!',
            ];

            $this->post(route('admin.users.store'), $userData)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.users'));

            $managedUser = User::where('email', $userData['email'])->firstOrFail();
            $this->assertTrue(Hash::check($userData['password'], $managedUser->password));
            $this->assertTrue($managedUser->roles()->where('slug', 'customer')->exists());

            $financeRole = Role::where('slug', 'finance')->firstOrFail();
            $this->put(route('admin.users.update', $managedUser), [
                'name' => 'Updated Managed User',
                'email' => $userData['email'],
                'role_id' => $financeRole->id,
            ])->assertRedirect(route('admin.users'));

            $this->assertDatabaseHas('users', [
                'id' => $managedUser->id,
                'name' => 'Updated Managed User',
            ]);
            $this->assertTrue($managedUser->fresh()->roles()->where('slug', 'finance')->exists());

            $this->delete(route('admin.users.destroy', $managedUser))
                ->assertRedirect(route('admin.users'));
            $this->assertDatabaseMissing('users', ['id' => $managedUser->id]);

            $productManager = $this->createUser('product-user-management-test@example.test', 'product_manager');
            $this->actingAs($productManager)->get('/admin/users')->assertForbidden();
        } finally {
            DB::rollBack();
        }
    }

    public function test_delivery_page_shows_vehicle_rates_and_coverage_map(): void
    {
        DB::beginTransaction();

        try {
            $vehicle = DeliveryVehicle::create([
                'name' => 'Test Motorbike',
                'max_weight_label' => 'Max Weight: 20 kg',
                'sort_order' => 1,
                'is_active' => true,
            ]);
            $vehicle->rates()->create([
                'distance_min_km' => 0,
                'distance_max_km' => 5,
                'fee' => 30000,
                'is_price_on_application' => false,
            ]);
            DeliveryCoverageArea::create([
                'name' => 'Denpasar Test Origin',
                'latitude' => -8.6705,
                'longitude' => 115.2126,
                'radius_km' => 45,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $this->get('/delivery?lang=id')
                ->assertOk()
                ->assertSee('Radius Pengiriman')
                ->assertSee('Test Motorbike')
                ->assertSee('Rp 30.000')
                ->assertSee('Denpasar Test Origin')
                ->assertSee('fa-truck-fast')
                ->assertSee('delivery-map')
                ->assertSee('text=Halo%2C%20saya%20mau%20bertanya%20tentang%20delivery%20Test%20Motorbike.', false)
                ->assertSee('/delivery?lang=en', false);
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_manage_delivery_vehicles_rates_and_coverage_origins(): void
    {
        DB::beginTransaction();
        Storage::fake('public');

        try {
            $admin = $this->createUser('admin-delivery-test@example.test', 'super_admin');
            $this->actingAs($admin)
                ->get(route('admin.delivery'))
                ->assertOk()
                ->assertSee('Delivery management');

            $vehicleData = [
                'name' => 'Test Pick-up',
                'max_weight_label' => 'Max Weight: 1 tonne',
                'sort_order' => 4,
                'is_active' => 1,
                'image' => UploadedFile::fake()->createWithContent(
                    'pickup.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                ),
            ];
            $this->post(route('admin.delivery.vehicles.store'), $vehicleData)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.delivery'));

            $vehicle = DeliveryVehicle::where('name', 'Test Pick-up')->firstOrFail();
            $this->assertNotNull($vehicle->image_path);
            Storage::disk('public')->assertExists($vehicle->image_path);

            $this->put(route('admin.delivery.vehicles.update', $vehicle), [
                'name' => 'Test Pick-up Updated',
                'max_weight_label' => 'Updated load',
                'sort_order' => 2,
                'is_active' => 1,
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.delivery'));

            $this->assertDatabaseHas('delivery_vehicles', [
                'id' => $vehicle->id,
                'name' => 'Test Pick-up Updated',
                'max_weight_label' => 'Updated load',
            ]);

            $this->post(route('admin.delivery.rates.store', $vehicle), [
                'distance_min_km' => 0,
                'distance_max_km' => 5,
                'fee' => 30000,
                'is_price_on_application' => 0,
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.delivery'));

            $rate = DeliveryRate::where('delivery_vehicle_id', $vehicle->id)->firstOrFail();
            $this->post(route('admin.delivery.rates.store', $vehicle), [
                'distance_min_km' => 4,
                'distance_max_km' => 8,
                'fee' => 40000,
                'is_price_on_application' => 0,
            ])->assertSessionHasErrors('distance_min_km');

            $this->put(route('admin.delivery.rates.update', [$vehicle, $rate]), [
                'distance_min_km' => 0,
                'distance_max_km' => 5,
                'fee' => '',
                'is_price_on_application' => 1,
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.delivery'));
            $this->assertDatabaseHas('delivery_rates', [
                'id' => $rate->id,
                'fee' => null,
                'is_price_on_application' => 1,
            ]);

            $this->post(route('admin.delivery.coverage-areas.store'), [
                'name' => 'Test Origin',
                'latitude' => -8.67,
                'longitude' => 115.21,
                'radius_km' => 45,
                'sort_order' => 1,
                'is_active' => 1,
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.delivery'));

            $area = DeliveryCoverageArea::where('name', 'Test Origin')->firstOrFail();
            $this->put(route('admin.delivery.coverage-areas.update', $area), [
                'name' => 'Updated Origin',
                'latitude' => -8.68,
                'longitude' => 115.22,
                'radius_km' => 35,
                'sort_order' => 2,
                'is_active' => 0,
            ])->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.delivery'));
            $this->assertDatabaseHas('delivery_coverage_areas', [
                'id' => $area->id,
                'name' => 'Updated Origin',
                'radius_km' => 35,
                'is_active' => 0,
            ]);

            $productManager = $this->createUser('product-delivery-test@example.test', 'product_manager');
            $this->actingAs($productManager)->get(route('admin.delivery'))->assertForbidden();
            $this->actingAs($admin);

            $this->delete(route('admin.delivery.coverage-areas.destroy', $area))
                ->assertRedirect(route('admin.delivery'));
            $this->delete(route('admin.delivery.vehicles.destroy', $vehicle))
                ->assertRedirect(route('admin.delivery'));
            $this->assertDatabaseMissing('delivery_vehicles', ['id' => $vehicle->id]);
            $this->assertDatabaseMissing('delivery_rates', ['id' => $rate->id]);
            $this->assertDatabaseMissing('delivery_coverage_areas', ['id' => $area->id]);
            Storage::disk('public')->assertMissing($vehicle->image_path);
        } finally {
            DB::rollBack();
        }
    }

    public function test_cart_selection_and_quantity_changes_return_live_selected_totals(): void
    {
        DB::beginTransaction();

        try {
            $customer = $this->createUser('cart-selection-test@example.test', 'customer');
            $category = Category::where('type', 'shop')->firstOrFail();
            $firstProduct = Product::create([
                'category_id' => $category->id,
                'name' => 'Selection Product One',
                'slug' => 'selection-product-one',
                'sku' => 'SEL-001',
                'description' => 'Cart selection test product.',
                'sale_price' => 100000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            $secondProduct = Product::create([
                'category_id' => $category->id,
                'name' => 'Selection Product Two',
                'slug' => 'selection-product-two',
                'sku' => 'SEL-002',
                'description' => 'Cart quantity test product.',
                'sale_price' => 50000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            $firstCartItem = CartItem::create([
                'user_id' => $customer->id,
                'product_id' => $firstProduct->id,
                'variant_key' => 0,
                'quantity' => 2,
                'is_selected' => true,
            ]);
            $secondCartItem = CartItem::create([
                'user_id' => $customer->id,
                'product_id' => $secondProduct->id,
                'variant_key' => 0,
                'quantity' => 3,
                'is_selected' => true,
            ]);

            $this->actingAs($customer)
                ->get(route('cart'))
                ->assertOk()
                ->assertSee('data-cart-selection', false)
                ->assertSee('name="quantity" value="2"', false)
                ->assertSee('data-cart-selected-subtotal>350.000', false);

            $this->postJson(route('cart.items.update'), [
                'cart_item_id' => $firstCartItem->id,
                'is_selected' => 0,
            ])->assertOk()
                ->assertJsonPath('is_selected', false)
                ->assertJsonPath('selected_subtotal', 150000)
                ->assertJsonPath('selected_item_count', 1);

            $this->postJson(route('cart.items.update'), [
                'cart_item_id' => $secondCartItem->id,
                'quantity' => 4,
            ])->assertOk()
                ->assertJsonPath('line_total', 200000)
                ->assertJsonPath('selected_subtotal', 200000);

            $this->assertDatabaseHas('cart_items', [
                'id' => $firstCartItem->id,
                'is_selected' => 0,
            ]);
            $this->assertDatabaseHas('cart_items', [
                'id' => $secondCartItem->id,
                'quantity' => 4,
                'is_selected' => 1,
            ]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_customer_can_submit_selected_cart_items_as_a_quote_without_payment(): void
    {
        DB::beginTransaction();

        try {
            $customer = $this->createUser('quote-submit-test@example.test', 'customer');
            $category = Category::where('type', 'shop')->firstOrFail();
            $selectedProduct = Product::create([
                'category_id' => $category->id,
                'name' => 'Quote Product Selected',
                'slug' => 'quote-product-selected',
                'sku' => 'QUOTE-001',
                'description' => 'Selected quote product.',
                'sale_price' => 125000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            $unselectedProduct = Product::create([
                'category_id' => $category->id,
                'name' => 'Quote Product Not Selected',
                'slug' => 'quote-product-not-selected',
                'sku' => 'QUOTE-002',
                'description' => 'Unselected quote product.',
                'sale_price' => 90000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            $selectedCartItem = CartItem::create([
                'user_id' => $customer->id,
                'product_id' => $selectedProduct->id,
                'variant_key' => 0,
                'quantity' => 2,
                'is_selected' => true,
            ]);
            $unselectedCartItem = CartItem::create([
                'user_id' => $customer->id,
                'product_id' => $unselectedProduct->id,
                'variant_key' => 0,
                'quantity' => 1,
                'is_selected' => false,
            ]);

            $this->actingAs($customer)
                ->get(route('customer.checkout'))
                ->assertOk()
                ->assertSee('Request a quotation')
                ->assertSee('Quote Product Selected')
                ->assertDontSee('Quote Product Not Selected')
                ->assertSee('does not take payment');

            $this->post(route('customer.quotations.store'), [
                'customer_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Jl. Test No. 10, Denpasar, Bali',
                'customer_note' => 'Call on arrival.',
            ])->assertSessionHasNoErrors()
                ->assertRedirect();

            $quotation = Quotation::with('items')->where('user_id', $customer->id)->firstOrFail();
            $this->assertSame('new', $quotation->status);
            $this->assertEquals(250000, (float) $quotation->total);
            $this->assertCount(1, $quotation->items);
            $this->assertSame('QUOTE-001', $quotation->items->first()->sku);
            $this->assertSame('+62 812-3456-7890', $customer->fresh()->phone);
            $this->assertDatabaseMissing('cart_items', ['id' => $selectedCartItem->id]);
            $this->assertDatabaseHas('cart_items', ['id' => $unselectedCartItem->id]);

            $this->get(route('customer.quotations'))
                ->assertOk()
                ->assertSee($quotation->quote_number)
                ->assertSee('New request');
            $this->get(route('customer.quotations.show', $quotation))
                ->assertOk()
                ->assertSee('Quote Product Selected')
                ->assertSee('https://wa.me/6285173323293?text=Hi%20BLA%2C%20I%20would%20like%20to%20ask%20about%20quotation', false);
            $this->get(route('customer.orders'))
                ->assertOk()
                ->assertSee('Orders will appear here');
            $otherCustomer = $this->createUser('other-quote-customer@example.test', 'customer');
            $this->actingAs($otherCustomer)
                ->get(route('customer.quotations.show', $quotation))
                ->assertNotFound();
        } finally {
            DB::rollBack();
        }
    }

    public function test_admin_can_revise_quotation_add_costs_discount_and_request_manual_payment(): void
    {
        DB::beginTransaction();

        try {
            $customer = $this->createUser('quote-admin-customer@example.test', 'customer');
            $category = Category::where('type', 'shop')->firstOrFail();
            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Customer Quote Base Product',
                'slug' => 'customer-quote-base-product',
                'sku' => 'ADMIN-QUOTE-001',
                'description' => 'Base quote product.',
                'sale_price' => 100000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);

            $this->actingAs($customer)->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])->assertRedirect();
            $this->post(route('customer.quotations.store'), [
                'customer_phone' => '+62 812-3456-7890',
                'shipping_address' => 'Original address in Denpasar',
                'customer_note' => 'Original customer note',
            ])->assertRedirect();

            $quotation = Quotation::with('items')->where('user_id', $customer->id)->firstOrFail();
            $baseItem = $quotation->items->firstOrFail();
            $additionalProduct = Product::create([
                'category_id' => $category->id,
                'name' => 'Admin Added Product',
                'slug' => 'admin-added-product',
                'sku' => 'ADMIN-QUOTE-002',
                'description' => 'Additional product.',
                'sale_price' => 200000,
                'for_sale' => true,
                'for_rental' => false,
                'is_active' => true,
            ]);
            $additionalVariant = ProductVariant::create([
                'product_id' => $additionalProduct->id,
                'sku' => 'ADMIN-QUOTE-002-V1',
                'price' => 200000,
                'stock_quantity' => 5,
                'is_active' => true,
            ]);
            $rentalCategory = Category::create([
                'name' => 'Admin Quote Rental',
                'slug' => 'admin-quote-rental',
                'type' => 'rental',
                'description' => 'Rental quote test category.',
                'is_active' => true,
            ]);
            $rentalProduct = Product::create([
                'category_id' => $rentalCategory->id,
                'name' => 'Admin Quote Rental Product',
                'slug' => 'admin-quote-rental-product',
                'sku' => 'ADMIN-RENTAL-001',
                'rental_price' => 75000,
                'for_sale' => false,
                'for_rental' => true,
                'is_active' => true,
            ]);
            $admin = $this->createUser('quote-admin-test@example.test', 'super_admin');
            $this->actingAs($admin)->get(route('admin.quotations'))
                ->assertOk()
                ->assertSee($quotation->quote_number)
                ->assertSee('New requests');
            $productManager = $this->createUser('quote-product-manager@example.test', 'product_manager');
            $this->actingAs($productManager)->get(route('admin.quotations'))->assertForbidden();
            $this->actingAs($admin);
            $this->get(route('admin.quotations.edit', $quotation))
                ->assertOk()
                ->assertSee('data-quote-item-dialog', false)
                ->assertSee('data-modal-product', false)
                ->assertSee('data-modal-variant', false)
                ->assertSee('data-modal-description', false)
                ->assertSee('data-modal-price', false)
                ->assertSee('select id="modal_item_type" data-modal-item-type>', false)
                ->assertSee('data-new-quote-item-template', false)
                ->assertSee('data-quote-step="-1"', false)
                ->assertSee('data-remove-quote-item', false)
                ->assertSee('data-quote-price', false)
                ->assertSee('value="rental">Rental</option>', false)
                ->assertSee('data-modal-rental-period', false)
                ->assertSee('data-for-rental="1"', false)
                ->assertSee('id="customer_name" value="BLA Test User" readonly', false)
                ->assertSee('id="customer_phone" type="tel" value="+62 812-3456-7890" readonly', false)
                ->assertDontSee('name="customer_name"', false)
                ->assertDontSee('name="customer_phone"', false);

            $updateQuotation = function (array $overrides = []) use ($quotation) {
                $quotation->refresh()->load('items');
                $items = [];
                foreach ($quotation->items as $item) {
                    $items[$item->id] = [
                        'id' => $item->id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ];
                    if ($item->item_type === 'rental') {
                        $rentalPeriod = collect($item->options_snapshot ?? [])
                            ->firstWhere('attribute', 'Rental period');
                        $items[$item->id]['rental_period'] = $rentalPeriod['value'] ?? 'daily';
                    }
                }
                $data = array_merge([
                    'customer_name' => $quotation->customer_name,
                    'customer_phone' => $quotation->customer_phone,
                    'shipping_address' => 'Updated Bali delivery address',
                    'customer_note' => $quotation->customer_note,
                    'admin_note' => 'Updated by BLA admin.',
                    'payment_instructions' => '',
                    'status' => 'under_review',
                    'items' => $items,
                ], $overrides);

                return $data;
            };

            $this->put(route('admin.quotations.update', $quotation), $updateQuotation([
                'customer_name' => 'Changed by admin',
                'customer_phone' => '1234567890',
                'items' => [
                    $baseItem->id => [
                        'id' => $baseItem->id,
                        'description' => 'Updated base product',
                        'quantity' => 3,
                        'unit_price' => 110000,
                    ],
                ],
                'new_items' => [[
                    'type' => 'product',
                    'product_id' => $additionalProduct->id,
                    'product_variant_id' => $additionalVariant->id,
                    'description' => 'Admin Added Product',
                    'quantity' => 1,
                    'unit_price' => 200000,
                ], [
                    'type' => 'service',
                    'description' => 'Installation labor',
                    'quantity' => 1,
                    'unit_price' => 400000,
                ], [
                    'type' => 'rental',
                    'product_id' => $rentalProduct->id,
                    'rental_period' => 'weekly',
                    'description' => 'Admin Quote Rental Product',
                    'quantity' => 2,
                    'unit_price' => 125000,
                ]],
            ]))->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.quotations.edit', $quotation));
            $this->assertDatabaseHas('quotations', [
                'id' => $quotation->id,
                'customer_name' => 'BLA Test User',
                'customer_phone' => '+62 812-3456-7890',
            ]);
            $this->assertDatabaseHas('quotation_items', [
                'quotation_id' => $quotation->id,
                'product_variant_id' => $additionalVariant->id,
                'description' => 'Admin Added Product',
            ]);
            $this->assertDatabaseHas('quotation_items', [
                'quotation_id' => $quotation->id,
                'item_type' => 'service',
                'service_id' => null,
                'description' => 'Installation labor',
                'unit_price' => 400000,
            ]);
            $rentalItem = QuotationItem::where('quotation_id', $quotation->id)
                ->where('item_type', 'rental')
                ->firstOrFail();
            $this->assertSame([
                ['attribute' => 'Rental period', 'value' => 'weekly'],
            ], $rentalItem->options_snapshot);
            $this->assertSame(2, $rentalItem->quantity);
            $this->assertEquals(250000, $rentalItem->line_total);

            $this->get(route('admin.quotations.edit', $quotation))
                ->assertOk()
                ->assertSee('item_rental_period_' . $rentalItem->id, false)
                ->assertSee('Weekly (7 days)');
            $rentalUpdate = $updateQuotation([
                'admin_note' => 'Reviewing the revised quote.',
            ]);
            $rentalUpdate['items'][$rentalItem->id]['rental_period'] = 'monthly';
            $this->put(route('admin.quotations.update', $quotation), $rentalUpdate)->assertSessionHasNoErrors();
            $rentalItem->refresh();
            $this->assertSame([
                ['attribute' => 'Rental period', 'value' => 'monthly'],
            ], $rentalItem->options_snapshot);

            $this->put(route('admin.quotations.update', $quotation), $updateQuotation([
                'new_item_type' => 'delivery',
                'new_description' => 'Delivery to Ubud',
                'new_quantity' => 1,
                'new_unit_price' => 50000,
            ]))->assertSessionHasNoErrors();

            $this->put(route('admin.quotations.update', $quotation), $updateQuotation([
                'new_item_type' => 'discount',
                'new_description' => 'Special discount',
                'new_quantity' => 1,
                'new_unit_price' => 100000,
                'status' => 'payment_requested',
                'payment_instructions' => 'Please transfer to the BLA business account.',
            ]))->assertSessionHasNoErrors()
                ->assertRedirect(route('admin.quotations.edit', $quotation));

            $quotation->refresh()->load('items', 'revisions');
            $this->assertSame('payment_requested', $quotation->status);
            $this->assertEquals(1130000, (float) $quotation->total);
            $this->assertEquals(1180000, (float) $quotation->subtotal);
            $this->assertEquals(50000, (float) $quotation->delivery_fee);
            $this->assertEquals(100000, (float) $quotation->discount_amount);
            $this->assertCount(5, $quotation->revisions);

            $this->get(route('admin.quotations.edit', $quotation))
                ->assertOk()
                ->assertSee('Send payment request on WhatsApp')
                ->assertSee('https://wa.me/6281234567890?text=', false)
                ->assertSee('Revision history');
            $this->actingAs($customer)
                ->get(route('customer.quotations.show', $quotation))
                ->assertOk()
                ->assertSee('Updated Bali delivery address')
                ->assertSee('Please transfer to the BLA business account.')
                ->assertSee('Monthly (28 days)')
                ->assertSee('Rp 1.130.000');

            $this->actingAs($admin)->put(route('admin.quotations.update', $quotation), $updateQuotation([
                'status' => 'payment_confirmed',
                'payment_instructions' => 'Please transfer to the BLA business account.',
            ]))->assertSessionHasNoErrors();
            $this->actingAs($customer)
                ->get(route('customer.orders'))
                ->assertOk()
                ->assertSee($quotation->quote_number)
                ->assertSee('Payment confirmed');
            $this->get(route('customer.quotations'))
                ->assertOk()
                ->assertDontSee($quotation->quote_number);
        } finally {
            DB::rollBack();
        }
    }

    private function createUser(string $email, string $roleSlug, string $password = 'TestAdminPassword123!'): User
    {
        $user = User::create([
            'name' => 'BLA Test User',
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
