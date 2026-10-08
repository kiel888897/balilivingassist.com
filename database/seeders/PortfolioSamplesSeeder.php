<?php

namespace Database\Seeders;

use App\Models\PortfolioImage;
use App\Models\PortfolioProject;
use Illuminate\Database\Seeder;

class PortfolioSamplesSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'AC Service & Maintenance',
                'slug' => 'sample-ac-service-maintenance',
                'description' => 'Illustrative sample gallery for split AC inspection, filter cleaning and routine maintenance. These are sample illustrations, not photos of a completed customer project.',
                'sort_order' => 10,
                'images' => [
                    ['portfolio/samples/ac-service-01.svg', 'Illustrative sample: wall-mounted air conditioner inspection'],
                    ['portfolio/samples/ac-service-02.svg', 'Illustrative sample: cleaning an air conditioner filter'],
                    ['portfolio/samples/ac-service-03.svg', 'Illustrative sample: servicing an outdoor AC condenser'],
                ],
            ],
            [
                'title' => 'Villa Interior Renovation',
                'slug' => 'sample-villa-interior-renovation',
                'description' => 'Illustrative sample gallery for a villa interior refresh, from preparation through finishing. Replace these visuals with real project photos before presenting completed work.',
                'sort_order' => 20,
                'images' => [
                    ['portfolio/samples/villa-renovation-01.svg', 'Illustrative sample: living room renovation in progress'],
                    ['portfolio/samples/villa-renovation-02.svg', 'Illustrative sample: refreshed villa living room'],
                ],
            ],
            [
                'title' => 'Garden & Poolside Care',
                'slug' => 'sample-garden-poolside-care',
                'description' => 'Illustrative sample gallery for outdoor property upkeep, garden care and poolside maintenance. These visuals are examples rather than photographs of a real project.',
                'sort_order' => 30,
                'images' => [
                    ['portfolio/samples/garden-care-01.svg', 'Illustrative sample: garden and path maintenance'],
                    ['portfolio/samples/garden-care-02.svg', 'Illustrative sample: clean villa poolside area'],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $project = PortfolioProject::firstOrCreate(
                ['slug' => $projectData['slug']],
                [
                    'title' => $projectData['title'],
                    'description' => $projectData['description'],
                    'is_active' => true,
                    'is_sample' => true,
                    'sort_order' => $projectData['sort_order'],
                ]
            );

            foreach ($projectData['images'] as $sortOrder => $image) {
                PortfolioImage::firstOrCreate(
                    [
                        'portfolio_project_id' => $project->id,
                        'sort_order' => $sortOrder,
                    ],
                    [
                        'image_path' => $image[0],
                        'alt_text' => $image[1],
                    ]
                );
            }
        }
    }
}
