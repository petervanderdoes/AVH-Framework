<?php
namespace Avh\Contracts\Foundation;

interface ApplicationInterface
{
    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string $provider
     * @param  array                                      $options
     * @param  bool                                       $force
     *
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = [], $force = false);

    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders();

    /**
     * Register a deferred provider and service.
     *
     * @param  string $provider
     * @param  string|null $service
     *
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null);

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version();
}
