<?php namespace Epikoder\Ocpaystack;

use App;
use Config;
use Illuminate\Foundation\AliasLoader;
use Backend;
use Epikoder\Ocpaystack\Classes\Paystack;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use System\Classes\PluginBase;

/**
 * ocpaystack Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'ocpaystack',
            'description' => 'No description provided yet...',
            'author'      => 'epikoder',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        $gateway = $this->app->get(PaymentGateway::class);
        $gateway->registerProvider(new Paystack());
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [];
    }
}
