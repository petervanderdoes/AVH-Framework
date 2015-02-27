<?php
/**
 * Created by PhpStorm.
 * User: pdoes
 * Date: 2/27/15
 * Time: 2:36 PM
 */

namespace Avh\Contracts\Foundation;


interface Application {
    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version();

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders();

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  array  $options
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = array(), $force = false);

    /**
     * Register a deferred provider and service.
     *
     * @param  string  $provider
     * @param  string  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null);
}
