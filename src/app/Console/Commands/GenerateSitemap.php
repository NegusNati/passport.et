<?php

namespace App\Console\Commands;

use Carbon\Carbon;
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

        // SitemapGenerator::create(config('app.url'))
        //     ->getSitemap()
        //     ->add(Url::create('/'))
        //     ->add(Url::create('/dashboard'))
        //     ->add(Url::create('/passport'))
        //     ->add(Url::create('/passport/search'))
        //     ->add(Url::create('/passport/{id}'))
        //     ->add(Url::create('/all-passports'))
        //     ->add(Url::create('/location'))
        //     ->add(Url::create('/location/{location}'))
        //     ->add(Url::create('/login'))
        //     ->add(Url::create('/register'))
        //     ->add(Url::create('/privacy'))
        //     ->add(Url::create('/telegram'))
        //     ->add(Url::create('/payment'))
        //     ->add(Url::create('/subscribe'))
        //     ->writeToFile(public_path('sitemap.xml'));


        SitemapGenerator::create(config('app.url'))
            ->getSitemap()
            ->add(Url::create('/')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/dashboard')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/passport')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/passport/search')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/passport/{id}')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/all-passports')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/locations')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/Gotera,%20Addis%20Ababa')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Gambela')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Jijiga')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Jimma')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Dire%20Dawa')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Dessie')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Adama')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Hawassa')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Asosa')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Bahir%20Dar')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Semera')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Mekele')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/location/ICS%20branch%20office,%20Hosaena')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/login')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/register')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/privacy')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/telegram')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/payment')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/subscribe')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->add(Url::create('/telegram')
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1))
            ->writeToFile(public_path('sitemap.xml'));
    }
}
