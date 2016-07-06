<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/28/16
 */

namespace StudentCentralApp\Providers;

use Collective\Html\HtmlServiceProvider;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\DomCrawler\Form;

class CustomMacroServiceProvider extends HtmlServiceProvider
{

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {

        $this->app->singleton('html', function ($app) {

            return new HtmlMacros($app['url'], $app['view']);
        });

    }
    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->singleton('form', function ($app) {
            $form = new FormMacros($app['html'], $app['url'], $app['view'], $app['session.store']->getToken());
            return $form->setSessionStore($app['session.store']);
        });
    }


}
