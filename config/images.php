<?php

return [
    'static' => [
        'source_dir' => resource_path('images/static'),
        'output_dir' => 'images/static',
        'manifest_path' => resource_path('js/images/staticImages.generated.json'),
        'quality' => 82,
        'widths' => [320, 480, 768, 1024, 1280, 1600, 1920, 2400],
        'mark_source_dir' => resource_path('images/marks'),
        'mark_widths' => [32, 48, 64, 96, 128],
        'limits' => [
            'allowed_mimes' => ['image/jpeg', 'image/png'],
            'max_bytes' => 8 * 1024 * 1024,
            'max_dimension' => 5000,
            'max_pixels' => 18_000_000,
            'min_dimension' => 1,
        ],
        'marks' => [
            'site-logo' => 'logo.png',
        ],
        'photos' => [
            'about-hero' => 'about-hero.jpg',
            'about-story' => 'about-story.jpg',
            'athlete-bag' => 'athlete-bag.jpg',
            'athlete-kick' => 'athlete-kick.jpg',
            'athlete-padwork' => 'athlete-padwork.jpg',
            'athlete-sparring' => 'athlete-sparring.jpg',
            'coach-portrait' => 'coach-portrait.jpg',
            'coaches-hero' => 'coaches-hero.jpg',
            'hero-kickboxing' => 'hero-kickboxing.jpg',
            'pad-work' => 'pad-work.jpg',
            'ring-training' => 'ring-training.jpg',
            'schedule-hero' => 'schedule-hero.jpg',
            'schedule-rhythm' => 'schedule-rhythm.jpg',
            'sparring' => 'sparring.jpg',
        ],
    ],
];
