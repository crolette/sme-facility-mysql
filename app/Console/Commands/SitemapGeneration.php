<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Location;
use Spatie\Crawler\Crawler;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapIndex;
use Psr\Http\Message\UriInterface;
use Spatie\Sitemap\SitemapGenerator;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SitemapGeneration extends Command
{
    // Define the command signature and description
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate Sitemap';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $languages = LaravelLocalization::getLocalesOrder();

        $routes = collect(Route::getRoutes()->getRoutesByName());
        $websiteRoutes = collect([]);
        $routes->map(
            function ($route, $key) use ($websiteRoutes) {
                if (str_contains($key, 'website')) {
                    $websiteRoutes->push($key);
                }
            }

        );

        $urlShouldntBeListed = ['website.locale', 'website.cgv', 'website.cgu', 'website.careers', 'website.legal', 'website.confidentiality', 'website.newsletter'];

        $websiteRoutes = $websiteRoutes->diff($urlShouldntBeListed);

        foreach ($languages as $key => $language) {
            $sitemapPath = public_path('/sitemap_' . $key . '.xml');

            $siteMap = new Sitemap;
            foreach ($websiteRoutes as $route) {
                $siteMap
                    ->add(
                        Url::create(
                            LaravelLocalization::localizeUrl(route($route), $key),
                            $key
                        )->addAlternate(
                            LaravelLocalization::localizeUrl(route($route), 'en'),
                            'en'
                        )->addAlternate(
                            LaravelLocalization::localizeUrl(route($route), 'fr'),
                            'fr'
                        )->addAlternate(
                            LaravelLocalization::localizeUrl(route($route), 'de'),
                            'de'
                        )->addAlternate(
                            LaravelLocalization::localizeUrl(route($route), 'nl'),
                            'nl'
                        )
                            ->setLastModificationDate(now())
                    )->writeToFile($sitemapPath);
            }
        }

        $sitemapIndexPath = public_path('sitemap.xml');
        SitemapIndex::create()
            ->add('sitemap_fr.xml')
            ->add('sitemap_de.xml')
            ->add('sitemap_en.xml')
            ->add('sitemap_nl.xml')

            ->writeToFile($sitemapIndexPath);
    }
}
