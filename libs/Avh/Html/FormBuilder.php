<?php
namespace Avh\Html;

use Avh\Html\HtmlBuilder;

class FormBuilder
{

    // @var Use tables to create FormBuilder
    private $use_table = false;

    private $option_name;

    private $nonce;

    private $html;

    /**
     * An array of label names we've created.
     *
     * @var array
     */
    protected $labels = array();

    public function __construct(\Avh\Html\HtmlBuilder $html)
    {
        $this->html = $html;
    }

    /**
     * Generates an opening HTML form tag.
     *
     * // FormBuilder will submit back to the current page using POST
     * echo FormBuilder::open();
     *
     * // FormBuilder will submit to 'search' using GET
     * echo FormBuilder::open('search', array('method' => 'get'));
     *
     * // When "file" inputs are present, you must include the "enctype"
     * echo FormBuilder::open(null, array('enctype' => 'multipart/form-data'));
     *
     * @param mixed $action
     *            form action, defaults to the current request URI, or [Request] class to use
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function open($action = null, $attributes = array())
    {
        if (!isset($attributes['method'])) {
            // Use POST method
            $attributes['method'] = 'post';
        }

        return '<form action="' . $action . '"' . $this->html->attributes($attributes) . '>';
    }

    /**
     * Creates the closing form tag.
     *
     * echo FormBuilder::close();
     *
     * @return string
     */
    public function close()
    {
        return '</form>';
    }

    public function openTable($attributes = array())
    {
        $this->use_table = true;
        $attributes = array_merge($attributes, array('class' => 'form-table'));
        return '<table' . $this->html->attributes($attributes) . '>';
    }

    public function closeTable()
    {
        $this->use_table = false;

        return '</table>';
    }

    /**
     * Create the nonce field.
     * Instead of using the standard WordPress function, we duplicate the function but using the methods of this class.
     * This will create a more standard looking HTML output.
     *
     * @param string $nonce
     * @param boolean $referer
     * @return string
     */
    public function fieldNonce($referer = true)
    {
        $nonce_field = $this->hidden('_wpnonce', wp_create_nonce($this->nonce));
        if ($referer) {
            $ref = $_SERVER['REQUEST_URI'];
            $nonce_field .= $this->hidden('_wp_http_referer', $ref);
        }

        return $nonce_field;
    }

    public function fieldSettings($action, $use_nonce = true)
    {
        $return = $this->hidden('action', $action);
        if ($use_nonce) {
            $return .= $this->fieldNonce();
        }

        return $return;
    }

    public function text($name, $value = null, $attributes = array())
    {
        $attributes['type'] = 'text';
        return $this->input($name, $value, $attributes);
    }

    public function checkboxes($name, $options, $attributes = array())
    {
        $attributes['type'] = 'checkbox';

        $output_field = '';
        foreach ($options as $value => $attr) {
            unset($attributes['checked']);
            if (isset($attr['checked']) && $attr['checked']) {
                $attributes['checked'] = 'checked';
            }
            if ($attr['value'] === false) {
                $attr['value'] = null;
            }
            $output_field .= $this->input(array($name => $value), $attr['value'], $attributes);
            $output_field .= $this->label($value, $attr['text']);
            $output_field .= '<br>';
        }
        $return = $this->outputField($output_field);

        return $return;
    }

    public function select($name, array $options = array(), $selected = null, $attributes = array())
    {
        $options['id'] = $this->getIdAttribute($name, $options);

        // Set the input name
        if (isset($this->option_name)) {
            $attributes['name'] = $this->option_name . '[' . $name . ']';
        } else {
            $attributes['name'] = $name;
        }

        // We will simply loop through the options and build an HTML value for each of
        // them until we have an array of HTML declarations. Then we will join them
        // all together into one single HTML element that can be put on the form.
        $html = array();

        foreach ($options as $value => $display) {
            $html[] = $this->getSelectOption($display, $value, $selected);
        }

        // Once we have all of this HTML, we can join this into a single element after
        // formatting the attributes into an HTML "attributes" string, then we will
        // build out a final select statement, which will contain all the values.
        $options = implode('', $html);

        return '<select' . $this->html->attributes($attributes) . '>' . $options . '</select>';
    }

    /**
     * Creates a hidden form input.
     *
     * echo FormBuilder::hidden('csrf', $token);
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function hidden($name, $value = null, $attributes = array(), $use_option_name = false)
    {
        $attributes['type'] = 'hidden';

        return $this->input($name, $value, $attributes, $use_option_name);
    }

    /**
     * Creates a button form input.
     * Note that the body of a button is NOT escaped,
     * to allow images and other HTML to be used.
     *
     * echo FormBuilder::button('save', 'Save Profile', array('type' => 'submit'));
     *
     * @param string $name
     *            input name
     * @param string $body
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function button($name, $body, $attributes = array())
    {
        // Set the input name
        $attributes['name'] = $name;

        return '<button' . $this->html->attributes($attributes) . '>' . $body . '</button>';
    }

    /**
     * Creates a submit form input.
     *
     * echo FormBuilder::submit(null, 'Login');
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses FormBuilder::input
     */
    public function submit($name, $value, $attributes = array())
    {
        $attributes['type'] = 'submit';

        return '<p class="submit">' . $this->input($name, $value, $attributes) . '</p>';
    }

    /**
     * Creates a password form input.
     *
     * echo FormBuilder::password('password');
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function password($name, $value = null, $attributes = array())
    {
        $attributes['type'] = 'password';

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a file upload form input.
     * No input value can be specified.
     *
     * echo FormBuilder::file('image');
     *
     * @param string $name
     *            input name
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function file($name, $attributes = array())
    {
        $attributes['type'] = 'file';

        return $this->input($name, null, $attributes);
    }

    /**
     * Creates a checkbox form input.
     *
     * echo FormBuilder::checkbox('remember_me', 1, (bool) $remember);
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param boolean $checked
     *            checked status
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function checkbox($name, $value = null, $checked = null, $attributes = array())
    {
        $attributes['type'] = 'checkbox';

        if ($checked === true) {
            // Make the checkbox active
            $attributes['checked'] = 'checked';
        }

        if ($value === false) {
            $value = null;
        }

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a radio form input.
     *
     * echo FormBuilder::radio('like_cats', 1, $cats);
     * echo FormBuilder::radio('like_cats', 0, ! $cats);
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param boolean $checked
     *            checked status
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses $this->input
     */
    public function radio($name, $value = null, $checked = false, $attributes = array())
    {
        $attributes['type'] = 'radio';

        if ($checked === true) {
            // Make the radio active
            $attributes[] = 'checked';
        }

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a textarea form input.
     *
     * echo FormBuilder::textarea('about', $about);
     *
     * @param string $name
     *            textarea name
     * @param string $body
     *            textarea body
     * @param array $attributes
     *            html attributes
     * @param boolean $double_encode
     *            encode existing HTML characters
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function textarea($name, $body = '', $attributes = array())
    {
        // Set the input name
        $attributes['name'] = $name;

        // Add default rows and cols attributes (required)
        $attributes += array('rows' => 10, 'cols' => 50);

        return '<textarea' . $this->html->attributes($attributes) . '>' . esc_textarea($body) . '</textarea>';
    }

    /**
     * Creates a image form input.
     *
     * echo FormBuilder::image(null, null, array('src' => 'media/img/login.png'));
     *
     * @param string $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @param boolean $index
     *            add index file to URL?
     * @return string
     * @uses $this->input
     */
    public function image($name, $value, $attributes = array())
    {
        $attributes['type'] = 'image';

        return $this->input($name, $value, $attributes);
    }

    // ____________PRIVATE FUNCTIONS____________

    /**
     * Creates a form input.
     * If no type is specified, a "text" type input will
     * be returned.
     *
     * echo FormBuilder::input('username', $username);
     *
     * @param string|array $name
     *            input name
     * @param string $value
     *            input value
     * @param array $attributes
     *            html attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    private function input($name, $value = null, $attributes = array(), $use_option_name = true)
    {
        // Set the input name
        if (isset($this->option_name) && $use_option_name) {
            if (!is_array($name)) {
                $attributes['name'] = $this->option_name . '[' . $name . ']';
                $id = $name;
            } else {
                $attributes['name'] = $this->option_name . '[' . key($name) . ']' . '[' . current($name) . ']';
                $id = current($name);
            }
        } else {
            $attributes['name'] = $name;
            $id = $name;
        }

        // Set the input value
        $attributes['value'] = $value;

        if (!isset($attributes['type'])) {
            // Default type is text
            $attributes['type'] = 'text';
        }

        if (!isset($attributes['id'])) {
            $attributes['id'] = $id;
        }

        return '<input' . $this->html->attributes($attributes) . ' />';
    }

    /**
     * Get the select option for the given value.
     *
     * @param string $display
     * @param string $value
     * @param string $selected
     * @return string
     */
    public function getSelectOption($display, $value, $selected)
    {
        if (is_array($display)) {
            return $this->optionGroup($display, $value, $selected);
        }

        return $this->option($display, $value, $selected);
    }

    /**
     * Create an option group form element.
     *
     * @param array $list
     * @param string $label
     * @param string $selected
     * @return string
     */
    protected function optionGroup($list, $label, $selected)
    {
        $html = array();

        foreach ($list as $value => $display) {
            $html[] = $this->option($display, $value, $selected);
        }

        return '<optgroup label="' . $label . '">' . implode('', $html) . '</optgroup>';
    }

    /**
     * Create a select element option.
     *
     * @param string $display
     * @param string $value
     * @param string $selected
     * @return string
     */
    protected function option($display, $value, $selected)
    {
        $selected = $this->getSelectedValue($value, $selected);

        $options = array('value' => $value, 'selected' => $selected);

        return '<option' . $this->html->attributes($options) . '>' . $display . '</option>';
    }

    /**
     * Creates a form label.
     * Label text is not automatically translated.
     *
     * echo FormBuilder::label('username', 'Username');
     *
     * @param string $name
     * @param string $display
     * @param array $attributes
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function label($name, $display = null, $attributes = array())
    {
        if ($display === null) {
            // Use the input name as the text
            $display = ucwords(str_replace('_', ' ', $name));
        }

        $this->labels[] = $name;

        // Set the label target
        $attributes['for'] = $name;

        return '<label' . $this->html->attributes($attributes) . '>' . $display . '</label>';
    }

    public function output($label, $field)
    {
        $output_return = $this->outputLabel($label);
        $output_return .= $this->outputField($field);

        return $output_return;
    }

    public function outputLabel($label)
    {
        if ($this->use_table) {
            return '<tr><th scope="row">' . $label . '</th>';
        } else {
            return $label;
        }
    }

    public function outputField($field)
    {
        if ($this->use_table) {
            return '<td>' . $field . '</td></tr>';
        } else {
            return $field;
        }
    }

    /**
     * Get the ID attribute for a field name.
     *
     * @param string $name
     * @param array $attributes
     * @return string
     */
    public function getIdAttribute($name, $attributes)
    {
        if (array_key_exists('id', $attributes)) {
            return $attributes['id'];
        }

        if (in_array($name, $this->labels)) {
            return $name;
        }
    }

    /**
     * Determine if the value is selected.
     *
     * @param string $value
     * @param string $selected
     * @return string
     */
    protected function getSelectedValue($value, $selected)
    {
        if (is_array($selected)) {
            return in_array($value, $selected) ? 'selected' : null;
        }

        return ((string) $value == (string) $selected) ? 'selected' : null;
    }
    // __________________________________________
    // ____________Setter and Getters____________
    // __________________________________________
    /**
     *
     * @param field_type $option_name
     */
    public function setOptionName($option_name)
    {
        $this->option_name = $option_name;
    }

    public function getOptionName()
    {
        return $this->option_name;
    }

    public function setNonceAction($nonce)
    {
        $this->nonce = $this->option_name . '-' . $nonce;
    }

    public function getNonceAction($nonce)
    {
        return $this->option_name . '-' . $nonce;
    }
}
