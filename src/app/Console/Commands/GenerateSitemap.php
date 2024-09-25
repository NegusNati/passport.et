<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // modify this to your own needs
        SitemapGenerator::create(config('app.url'))
            ->getSitemap()
            ->add(Url::create('/'))
            ->add(Url::create('/dashboard'))
            ->add(Url::create('/passport'))
            ->add(Url::create('/passport/search'))
            ->add(Url::create('/passport'))
            ->add(Url::create('/passport/{id}'))
            ->add(Url::create('/all-passports'))
            ->add(Url::create('/login'))
            ->add(Url::create('/register'))
            ->add(Url::create('/privacy'))
            ->add(Url::create('/telegram'))
            ->add(Url::create('/payment'))
            ->add(Url::create('/subscribe'))
            ->writeToFile(public_path('sitemap.xml'));
    }
}
