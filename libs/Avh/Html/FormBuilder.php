<?php
namespace Avh\Html;


class FormBuilder
{

    // @var Use tables to create FormBuilder
    /**
     * An array of label names we've created.
     *
     * @var array
     */
    protected $labels = array();
    private $html;
    private $option_name;
    private $use_table = false;

    public function __construct(HtmlBuilder $html)
    {
        $this->html = $html;
    }


    /**
     * Creates a button form input.
     * Note that the body of a button is NOT escaped,
     * to allow images and other HTML to be used.
     *
     * echo FormBuilder::button('save', 'Save Profile', array('type' => 'submit'));
     *
     * @param string $name       input name
     * @param string $body       input value
     * @param array  $attributes html attributes
     *
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
     * Creates a checkbox form input.
     *
     * echo FormBuilder::checkbox('remember_me', 1, (bool) $remember);
     *
     * @param string  $name       input name
     * @param string  $value      input value
     * @param boolean $checked    checked status
     * @param array   $attributes html attributes
     *
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
            $label_field = $this->label($value, $attr['text']);
            $input_field = $this->input(array($name => $value), $attr['value'], $attributes);
            $output_field .= $input_field . $label_field . '<br>';
        }
        $return = $this->outputField($output_field);

        return $return;
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

    public function closeTable()
    {
        $this->use_table = false;

        return '</table>';
    }

    public function deleteOptionName()
    {
        $this->option_name = null;
    }

    /**
     * Create the nonce field.
     * Instead of using the standard WordPress function, we duplicate the function but using the methods of this class.
     * This will create a more standard looking HTML output.
     *
     * @param string  $nonce
     * @param boolean $referrer
     *
     * @return string
     */
    public function fieldNonce($nonce, $referrer = true)
    {
        $nonce_field = $this->hidden('_wpnonce', wp_create_nonce($nonce), array('id' => null));
        if ($referrer) {
            $ref = $_SERVER['REQUEST_URI'];
            $nonce_field .= $this->hidden('_wp_http_referer', $ref, array('id' => null));
        }

        return $nonce_field;
    }

    /**
     * Creates a file upload form input.
     * No input value can be specified.
     *
     * echo FormBuilder::file('image');
     *
     * @param string $name       input name
     * @param array  $attributes html attributes
     *
     * @return string
     * @uses $this->input
     */
    public function file($name, $attributes = array())
    {
        $attributes['type'] = 'file';

        return $this->input($name, null, $attributes);
    }

    /**
     * Get the ID attribute for a field name.
     *
     * @param string $name
     * @param array  $attributes
     *
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

    public function getOptionName()
    {
        return $this->option_name;
    }

    /**
     *
     * @param field_type $option_name
     */
    public function setOptionName($option_name)
    {
        $this->option_name = $option_name;
    }

    /**
     * Get the select option for the given value.
     *
     * @param string $display
     * @param string $value
     * @param string $selected
     *
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
     * Creates a hidden form input.
     *
     * echo FormBuilder::hidden('csrf', $token);
     *
     * @param string $name       input name
     * @param string $value      input value
     * @param array  $attributes html attributes
     *
     * @return string
     * @uses $this->input
     */
    public function hidden($name, $value = null, $attributes = array(), $use_option_name = false)
    {
        $attributes['type'] = 'hidden';

        return $this->input($name, $value, $attributes, $use_option_name);
    }

    /**
     * Creates a image form input.
     *
     * echo FormBuilder::image(null, null, array('src' => 'media/img/login.png'));
     *
     * @param string  $name       input name
     *
     * @param string  $value      input value
     * @param array   $attributes html attributes
     * @param boolean $index      add index file to URL?
     *
     * @return string
     * @uses $this->input
     */
    public function image($name, $value, $attributes = array())
    {
        $attributes['type'] = 'image';

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a form input.
     * If no type is specified, a "text" type input will
     * be returned.
     *
     * echo FormBuilder::input('username', $username);
     *
     * @param string|array $name       input name
     * @param string       $value      input value
     * @param array        $attributes html attributes
     *
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function input($name, $value = null, $attributes = array(), $use_option_name = true)
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

        if (!array_key_exists('type', $attributes)) {
            // Default type is text
            $attributes['type'] = 'text';
        }

        $attributes['id'] = $this->getIdAttribute($id, $attributes);

        return '<input' . $this->html->attributes($attributes) . ' />';
    }

    /**
     * Creates a form label.
     * Label text is not automatically translated.
     *
     * echo FormBuilder::label('username', 'Username');
     *
     * @param string $name
     * @param string $display
     * @param array  $attributes
     *
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
     * @param mixed $action     form action, defaults to the current request URI, or [Request] class to use
     * @param array $attributes html attributes
     *
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function open($action = null, $attributes = array())
    {
        $attributes['method'] = $this->getMethod(avh_array_get($attributes, 'method', 'post'));

        $attributes['accept-charset'] = $this->getCharset(avh_array_get($attributes, 'accept-charset'));

        return '<form action="' . $action . '"' . $this->html->attributes($attributes) . '>';
    }

    public function openTable($attributes = array())
    {
        $this->use_table = true;
        $attributes = array_merge($attributes, array('class' => 'form-table'));

        return '<table' . $this->html->attributes($attributes) . '>';
    }

    public function output($label, $field)
    {
        $output_return = $this->outputLabel($label);
        $output_return .= $this->outputField($field);

        return $output_return;
    }

    // ____________PRIVATE FUNCTIONS____________

    public function outputField($field)
    {
        if ($this->use_table) {
            return '<td>' . $field . '</td></tr>';
        } else {
            return $field;
        }
    }

    public function outputLabel($label)
    {
        if ($this->use_table) {
            return '<tr><th scope="row">' . $label . '</th>';
        } else {
            return $label;
        }
    }

    /**
     * Creates a password form input.
     *
     * echo FormBuilder::password('password');
     *
     * @param string $name       input name
     * @param string $value      input value
     * @param array  $attributes html attributes
     *
     * @return string
     * @uses $this->input
     */
    public function password($name, $attributes = array())
    {
        $attributes['type'] = 'password';

        return $this->input($name, null, $attributes);
    }

    /**
     * Creates a radio form input.
     *
     * echo FormBuilder::radio('like_cats', 1, $cats);
     * echo FormBuilder::radio('like_cats', 0, ! $cats);
     *
     * @param string  $name       input name
     * @param string  $value      input value
     * @param boolean $checked    checked status
     * @param array   $attributes html attributes
     *
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

    public function select($name, array $options = array(), $selected = null, $attributes = array())
    {
        $attributes['id'] = $this->getIdAttribute($name, $attributes);

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
     * Creates a submit form input.
     *
     * echo FormBuilder::submit(null, 'Login');
     *
     * @param string $name       input name
     * @param string $value      input value
     * @param array  $attributes html attributes
     *
     * @return string
     * @uses FormBuilder::input
     */
    public function submit($name, $value, $attributes = array())
    {
        $attributes['type'] = 'submit';

        return '<p class="submit">' . $this->input($name, $value, $attributes) . '</p>';
    }

    public function text($name, $value = null, $attributes = array())
    {
        $attributes['type'] = 'text';

        return $this->input($name, $value, $attributes);
    }

    /**
     * Creates a textarea form input.
     *
     * echo FormBuilder::textarea('about', $about);
     *
     * @param string  $name          textarea name
     * @param string  $body          textarea body
     * @param array   $attributes    html attributes
     * @param boolean $double_encode encode existing HTML characters
     *
     * @return string
     * @uses HtmlBuilder->attributes
     */
    public function textarea($name, $body = '', $attributes = array())
    {
        // Set the input name
        $attributes['name'] = $name;

        // Next we will look for the rows and cols attributes, as each of these are put
        // on the textarea element definition. If they are not present, we will just
        // assume some sane default values for these attributes for the developer.
        $attributes = $this->setTextAreaSize($attributes);
        unset($attributes['size']);

        $attributes['id'] = $this->getIdAttribute($name, $attributes);

        return '<textarea' . $this->html->attributes($attributes) . '>' . esc_textarea($body) . '</textarea>';
    }


    protected function getCharset($charset)
    {
        return $charset !== null ? $charset : get_bloginfo('charset', 'display');
    }

    /**
     * Parse the form action method.
     *
     * @param  string $method
     *
     * @return string
     */
    protected function getMethod($method)
    {
        $method = strtoupper($method);

        return $method != 'GET' ? 'POST' : $method;
    }

    /**
     * Determine if the value is selected.
     *
     * @param string $value
     * @param string $selected
     *
     * @return string
     */
    protected function getSelectedValue($value, $selected)
    {
        if (is_array($selected)) {
            return in_array($value, $selected) ? 'selected' : null;
        }

        return ((string) $value == (string) $selected) ? 'selected' : null;
    }

    /**
     * Create a select element option.
     *
     * @param string $display
     * @param string $value
     * @param string $selected
     *
     * @return string
     */
    protected function option($display, $value, $selected)
    {
        $selected = $this->getSelectedValue($value, $selected);

        $options = array('value' => $value, 'selected' => $selected);

        return '<option' . $this->html->attributes($options) . '>' . $display . '</option>';
    }
    // __________________________________________
    // ____________Setter and Getters____________
    // __________________________________________

    /**
     * Create an option group form element.
     *
     * @param array  $list
     * @param string $label
     * @param string $selected
     *
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
     * Set the text area size using the quick "size" attribute.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function setQuickTextAreaSize($attributes)
    {
        $segments = explode('x', $attributes['size']);

        return array_merge($attributes, array('cols' => $segments[0], 'rows' => $segments[1]));
    }

    /**
     * Set the text area size on the attributes.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function setTextAreaSize($attributes)
    {
        if (isset($attributes['size'])) {
            return $this->setQuickTextAreaSize($attributes);
        }

        // If the "size" attribute was not specified, we will just look for the regular
        // columns and rows attributes, using sane defaults if these do not exist on
        // the attributes array. We'll then return this entire options array back.
        $cols = avh_array_get($attributes, 'cols', 50);

        $rows = avh_array_get($attributes, 'rows', 10);

        return array_merge($attributes, compact('cols', 'rows'));
    }
}
