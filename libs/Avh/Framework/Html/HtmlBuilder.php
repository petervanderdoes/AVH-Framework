<?php
namespace Avh\Framework\Html;

/**
 * HTML helper class.
 * Provides generic methods for generating various HTML
 * tags and making output HTML safe.
 */
class HtmlBuilder
{
    /**
     * @var boolean automatically target external URLs to a new window?
     */
    public $windowed_urls = false;
    /**
     * @var array preferred order of attributes
     */
    private $attribute_order = [
        'action',
        'method',
        'type',
        'id',
        'name',
        'value',
        'href',
        'src',
        'width',
        'height',
        'cols',
        'rows',
        'size',
        'maxlength',
        'rel',
        'media',
        'accept-charset',
        'accept',
        'tabindex',
        'accesskey',
        'alt',
        'title',
        'class',
        'style',
        'selected',
        'checked',
        'readonly',
        'disabled'
    ];

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Create HTML link anchors.
     * Note that the title is not escaped, to allow
     * HTML elements within links (images, etc).
     *
     * @param string      $uri        URL or URI string
     * @param string|null $title      link text
     * @param array       $attributes HTML anchor attributes
     *
     * @return string
     */
    public function anchor($uri, $title = null, $attributes = [])
    {
        $url = $this->generateUrl($uri);

        // Add the sanitized link to the attributes
        $attributes['href'] = $url;

        if ($title === null) {
            $title = $url;
        }

        return '<a' . $this->attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Compiles an array of HTML attributes into an attribute string.
     * Attributes will be sorted using AVH_Html::$attribute_order for consistency.
     * echo '<div'.HtmlBuilder->attributes($attrs).'>'.$content.'</div>';
     *
     * @param array $attributes attribute list
     *
     * @return string
     */
    public function attributes($attributes = [])
    {
        if (empty($attributes)) {
            return '';
        }

        $sorted = [];
        foreach ($this->attribute_order as $key) {
            if (isset($attributes[$key])) {
                // Add the attribute to the sorted list
                $sorted[$key] = $attributes[$key];
            }
        }

        // Combine the sorted attributes
        $attributes = $sorted + $attributes;

        $compiled = '';
        foreach ($attributes as $key => $value) {
            $element = $this->attributeElement($key, $value);
            if ($element !== null) {
                $compiled .= ' ' . $element;
            }
        }

        return $compiled;
    }

    /**
     * Create a closing tag for the given element.
     *
     * @param string $element
     *
     * @return string
     */
    public function closeElement($element)
    {
        return '</' . $element . '>';
    }

    /**
     * Create a opening tag for the given element.
     *
     * @param string $element
     * @param array  $attributes
     * @param bool   $closetag
     *
     * @return string
     */
    public function element($element, $attributes = [], $closetag = false)
    {
        $return = '<' . $element . $this->attributes($attributes) . '>';
        if ($closetag) {
            $return .= $this->closeElement($element);
        }

        return $return;
    }

    /**
     * Create an URL to be used in plugins.
     *
     * @param string $uri
     *
     * @return string
     */
    public function generateUrl($uri)
    {
        if ($uri === '') {
            // Only use the base URL
            $uri = home_url('/');
        } else {
            if (strpos($uri, '://') === false) {
                // Make the URI absolute for non-id anchors
                $uri = plugins_url($uri);
            }
        }

        return $uri;
    }

    /**
     * Creates a image link.
     *
     * @param string      $file       file name
     * @param string|null $alt
     * @param array       $attributes default attributes
     *
     * @return string
     */
    public function image($file, $alt = null, $attributes = [])
    {
        if (empty($file)) {
            throw new \InvalidArgumentException('File can not be empty');
        }

        $url = $this->generateUrl($file);

        // Add the image link
        $attributes['src'] = $url;
        $attributes['alt'] = $alt;

        return '<img' . $this->attributes($attributes) . '>';
    }

    /**
     * Creates an email (mailto:) anchor.
     * Note that the title is not escaped,
     * to allow HTML elements within links (images, etc).
     *
     * @param string      $email      email address to send to
     * @param string|null $title      link text
     * @param array       $attributes HTML anchor attributes
     *
     * @return string
     */
    public function mailto($email, $title = null, $attributes = [])
    {
        if ($title === null) {
            // Use the email address as the title
            $title = $email;
        }

        $email = $this->obfuscate('mailto:') . $email;

        return '<a href="' . $email . '"' . $this->attributes($attributes) . '>' . esc_html($title) . '</a>';
    }

    /**
     * Obfuscate a string to prevent spam-bots from sniffing it.
     *
     * @param string $value
     *
     * @return string
     */
    public function obfuscate($value)
    {
        $safe = '';

        foreach (str_split($value) as $letter) {
            // To properly obfuscate the value, we will randomly convert each letter to
            // its entity or hexadecimal representation, keeping a bot from sniffing
            // the randomly obfuscated letters out of the string on the responses.
            switch (mt_rand(1, 3)) {
                case 1:
                    $safe .= '&#' . ord($letter) . ';';
                    break;

                case 2:
                    $safe .= '&#x' . dechex(ord($letter)) . ';';
                    break;

                case 3:
                    $safe .= $letter;
            }
        }

        return $safe;
    }

    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     *
     * @return string|null
     */
    protected function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            $key = $value;
        }

        if ($value !== null) {
            return $key . '="' . esc_attr($value) . '"';
        }

        return null;
    }
}
