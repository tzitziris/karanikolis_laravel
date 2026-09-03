<?php

return [
    'static' => [
        'source_dir' => env(
            'STATIC_IMAGE_SOURCE_DIR',
            base_path('../karanikolis_site/public/media'),
        ),
        'output_dir' => 'images/static',
        'quality' => 82,
        'widths' => [320, 480, 768, 1024, 1280, 1600, 1920, 2400],
        'limits' => [
            'allowed_mimes' => ['image/jpeg'],
            'max_bytes' => 8 * 1024 * 1024,
            'max_dimension' => 5000,
            'max_pixels' => 18_000_000,
            'min_dimension' => 1,
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
