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
        $shortcode_controller = $this->shortcode_map[$tag]['shortcode_controller'];

        if ($shortcode_controller === null) {
            $class = $this->shortcode_controller;
        } else {
            $class = $this->container->make($shortcode_controller);
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
     * @param string $shortcode_controller
     */
    public function register($tag, $method, $shortcode_controller = null)
    {
        $this->shortcode_map[$tag] = ['shortcode_controller' => $shortcode_controller, 'method' => $method];
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
