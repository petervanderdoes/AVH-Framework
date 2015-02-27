<?php
namespace Avh\Utility;

/**
 * Class ShortcodesAbstract
 *
 * @package Avh\Utility
 */
abstract class ShortcodesAbstract
{
    private $container;
    private $shortcode_controller;
    private $shortcode_map;
    private $service_provider;

    /**
     * Method that's always called for the shortcode
     *
     * @param  array  $atts
     * @param  string $content
     * @param  string $tag
     *
     * @return string
     */
    public function bootstrap($atts, $content, $tag)
    {
        $method = $this->shortcode_map[$tag]['method'];
        $container_name = $this->shortcode_map[$tag]['container_name'];

        if ($container_name === null) {
            $class = $this->shortcode_controller;
        } else {
            $class = $this->container->make($container_name);
        }

        $html = '';
        if (is_callable([$class, $method])) {
            // In case the shortcode echo the output instead of returning the output
            ob_start();
            $html = $class->$method($atts, $content, $tag);
            $html .= ob_get_clean();
        }

        return $html;
    }

    /**
     * Register a shortcode
     *
     * @param string $tag
     * @param string $container_name
     */
    public function register($tag, $method, $container_name = null)
    {
        $this->shortcode_map[$tag] = ['container_name' => $container_name, 'method' => $method];
        add_shortcode($tag, [$this, 'bootstrap']);
    }

    /**
     * @param object $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed $shortcode_controller
     */
    public function setShortcodeController($shortcode_controller)
    {
        $this->shortcode_controller = $shortcode_controller;
    }
}
