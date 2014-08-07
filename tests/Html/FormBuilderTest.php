<?php
use Avh\Html\FormBuilder;
use Avh\Html\HtmlBuilder;
use Illuminate\Container\Container;

class FormBuilderTest extends WP_UnitTestCase
{

    /**
     *
     * @var FormBuilder
     */
    public $formBuilder;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $container = new Container();

        $this->formBuilder = $container->make('Avh\Html\FormBuilder');
        parent::setUp();
    }

    public function testFormButton()
    {
        $form1 = $this->formBuilder->button('foo', 'FooBar');
        $form2 = $this->formBuilder->button('foo', 'FooBar', array('class' => 'span2'));

        $this->assertEquals('<button name="foo">FooBar</button>', $form1);
        $this->assertEquals('<button name="foo" class="span2">FooBar</button>', $form2);
    }

    public function testFormCheckbox()
    {
        $form1 = $this->formBuilder->checkbox('foo');
        $form2 = $this->formBuilder->checkbox('foo', false);
        $form3 = $this->formBuilder->checkbox('foo', 'foobar', true);
        $form4 = $this->formBuilder->checkbox('foo', 'foobar', false, array('class' => 'span2'));

        $this->assertEquals('<input type="checkbox" name="foo" />', $form1);
        $this->assertEquals($form1, $form2);
        $this->assertEquals('<input type="checkbox" name="foo" value="foobar" checked="checked" />', $form3);
        $this->assertEquals('<input type="checkbox" name="foo" value="foobar" class="span2" />', $form4);
    }

    public function testFormRadio()
    {
        $form1 = $this->formBuilder->radio('foo');
        $form2 = $this->formBuilder->radio('foo', 'foobar', true);
        $form3 = $this->formBuilder->radio('foo', 'foobar', false, array('class' => 'span2'));

        $this->assertEquals('<input type="radio" name="foo" />', $form1);
        $this->assertEquals('<input type="radio" name="foo" value="foobar" checked="checked" />', $form2);
        $this->assertEquals('<input type="radio" name="foo" value="foobar" class="span2" />', $form3);
    }

    public function testFieldNonce()
    {
        $nonce = wp_create_nonce(null);
        $form1 = $this->formBuilder->fieldNonce(null);
        $form2 = $this->formBuilder->fieldNonce(null,false);

        $this->assertEquals('<input type="hidden" name="_wpnonce" value="' . $nonce . '" /><input type="hidden" name="_wp_http_referer" value="" />', $form1);
        $this->assertEquals('<input type="hidden" name="_wpnonce" value="' . $nonce . '" />', $form2);
    }

    public function testFormCheckboxes()
    {
        $array_cb1 = array('cb_1' => array('text' => 'First', 'value' => '1', 'checked' => '1'));
        $array_cb2 = array('cb_2' => array('text' => 'Second', 'value' => '1', 'checked' => '0'));
        $array_cb3 = array('cb_3' => array('text' => 'Third', 'value' => '0', 'checked' => '0'));
        $array_cb4 = array('cb_4' => array('text' => 'CB', 'value' => false, 'checked' => '0'));
        $array_cb5 = array('cb_5' => array('text' => 'CB', 'value' => false, 'checked' => true));

        $this->formBuilder->setOptionName('option');
        $form1 = $this->formBuilder->checkboxes('cb_name', $array_cb1);
        $form2 = $this->formBuilder->checkboxes('cb_name', $array_cb2);
        $form3 = $this->formBuilder->checkboxes('cb_name', $array_cb3);
        $form4 = $this->formBuilder->checkboxes('cb_name', $array_cb4);
        $form5 = $this->formBuilder->checkboxes('cb_name', $array_cb5);
        $form6 = $this->formBuilder->checkboxes('cb_name', $array_cb5, array('class' => 'class_1'));
        $this->formBuilder->deleteOptionName();

        $expected1 = '<input type="checkbox" id="cb_1" name="option[cb_name][cb_1]" value="1" checked="checked" /><label for="cb_1">First</label><br>';
        $expected2 = '<input type="checkbox" id="cb_2" name="option[cb_name][cb_2]" value="1" /><label for="cb_2">Second</label><br>';
        $expected3 = '<input type="checkbox" id="cb_3" name="option[cb_name][cb_3]" value="0" /><label for="cb_3">Third</label><br>';
        $expected4 = '<input type="checkbox" id="cb_4" name="option[cb_name][cb_4]" /><label for="cb_4">CB</label><br>';
        $expected5 = '<input type="checkbox" id="cb_5" name="option[cb_name][cb_5]" checked="checked" /><label for="cb_5">CB</label><br>';
        $expected6 = '<input type="checkbox" id="cb_5" name="option[cb_name][cb_5]" class="class_1" checked="checked" /><label for="cb_5">CB</label><br>';

        $this->assertEquals($expected1, $form1);
        $this->assertEquals($expected2, $form2);
        $this->assertEquals($expected3, $form3);
        $this->assertEquals($expected4, $form4);
        $this->assertEquals($expected5, $form5);
        $this->assertEquals($expected6, $form6);
    }

    public function testFormOpen()
    {
        $form1 = $this->formBuilder->open();
        $form2 = $this->formBuilder->open('http://localhost/foo', array('method' => 'GET'));
        $form3 = $this->formBuilder->open('http://localhost/foo', array('method' => 'GET'));
        $form4 = $this->formBuilder->open('http://localhost/foo', array('method' => 'POST', 'class' => 'form', 'id' => 'id-form'));
        $form5 = $this->formBuilder->open('http://localhost/foo', array('method' => 'GET', 'accept-charset' => 'UTF-16'));
        $form6 = $this->formBuilder->open('http://localhost/foo', array('method' => 'GET', 'accept-charset' => 'UTF-16', 'files' => true));
        $form7 = $this->formBuilder->open('http://localhost/foo', array('method' => 'PUT'));

        $this->assertEquals('<form action="" method="POST" accept-charset="UTF-8">', $form1);
        $this->assertEquals('<form action="http://localhost/foo" method="GET" accept-charset="UTF-8">', $form2);
        $this->assertEquals($form2, $form3);
        $this->assertEquals('<form action="http://localhost/foo" method="POST" id="id-form" accept-charset="UTF-8" class="form">', $form4);
        $this->assertEquals('<form action="http://localhost/foo" method="GET" accept-charset="UTF-16">', $form5);
        $this->assertEquals('<form action="http://localhost/foo" method="GET" accept-charset="UTF-16" files="1">', $form6);
        $this->assertEquals('<form action="http://localhost/foo" method="POST" accept-charset="UTF-8">', $form7);
    }

    public function testFormClose()
    {
        $this->assertEquals('</form>', $this->formBuilder->close());
    }

    public function testFormOpenTable()
    {
        $this->assertEquals('<table class="form-table">', $this->formBuilder->openTable());
    }

    public function testFormCloseTable()
    {
        $this->assertEquals('</table>', $this->formBuilder->closeTable());
    }

    public function testFormLabel()
    {
        $form1 = $this->formBuilder->label('foo_2');
        $form2 = $this->formBuilder->label('foo', 'Foobar');
        $form3 = $this->formBuilder->label('foo', 'Foobar', array('class' => 'control-label'));

        $this->assertEquals('<label for="foo_2">Foo 2</label>', $form1);
        $this->assertEquals('<label for="foo">Foobar</label>', $form2);
        $this->assertEquals('<label class="control-label" for="foo">Foobar</label>', $form3);
    }

    public function testFormInput()
    {

        $form1 = $this->formBuilder->input('foo');
        $form2 = $this->formBuilder->input('foo', 'foobar');
        $this->formBuilder->setOptionName('option');
        $form3 = $this->formBuilder->input('foo', 'foobar');
        $form4 = $this->formBuilder->input(array('foo'=>'bar'), 'foobar');
        $this->formBuilder->deleteOptionName();
        $form5 = $this->formBuilder->input('foobar', null, array('class' => 'span2'));

        $this->assertEquals('<input type="text" name="foo" />', $form1);
        $this->assertEquals('<input type="text" name="foo" value="foobar" />', $form2);
        $this->assertEquals('<input type="text" name="option[foo]" value="foobar" />', $form3);
        $this->assertEquals('<input type="text" name="option[foo][bar]" value="foobar" />', $form4);
        $this->assertEquals('<input type="text" name="foobar" class="span2" />', $form5);
    }

    public function testFormText()
    {
        $form1 = $this->formBuilder->input('foo');
        $form2 = $this->formBuilder->text('foo');
        $form3 = $this->formBuilder->text('foo', 'foobar');
        $form4 = $this->formBuilder->text('foo', null, array('class' => 'span2'));

        $this->assertEquals('<input type="text" name="foo" />', $form1);
        $this->assertEquals($form1, $form2);
        $this->assertEquals('<input type="text" name="foo" value="foobar" />', $form3);
        $this->assertEquals('<input type="text" name="foo" class="span2" />', $form4);
    }

    public function testFormTextarea()
    {
        $form1 = $this->formBuilder->textarea('foo');
        $form2 = $this->formBuilder->textarea('foo', 'foobar');
        $form3 = $this->formBuilder->textarea('foo', null, array('class' => 'span2'));
        $form4 = $this->formBuilder->textarea('foo', null, array('size' => '60x15'));

        $this->assertEquals('<textarea name="foo" cols="50" rows="10"></textarea>', $form1);
        $this->assertEquals('<textarea name="foo" cols="50" rows="10">foobar</textarea>', $form2);
        $this->assertEquals('<textarea name="foo" cols="50" rows="10" class="span2"></textarea>', $form3);
        $this->assertEquals('<textarea name="foo" cols="60" rows="15"></textarea>', $form4);
    }

    public function testFormImage()
    {
        $form1 = $this->formBuilder->image('foo', 'foobar');
        $form2 = $this->formBuilder->image('foo', null, array('class' => 'span2'));

        $this->assertEquals('<input type="image" name="foo" value="foobar" />', $form1);
        $this->assertEquals('<input type="image" name="foo" class="span2" />', $form2);
    }

    public function testFormFile()
    {
        $form1 = $this->formBuilder->file('foo');
        $form2 = $this->formBuilder->file('foo', array('class' => 'span2'));

        $this->assertEquals('<input type="file" name="foo" />', $form1);
        $this->assertEquals('<input type="file" name="foo" class="span2" />', $form2);
    }

    public function testFormPassword()
    {
        $form1 = $this->formBuilder->password('foo');
        $form2 = $this->formBuilder->password('foo', array('class' => 'span2'));

        $this->assertEquals('<input type="password" name="foo" />', $form1);
        $this->assertEquals('<input type="password" name="foo" class="span2" />', $form2);
    }

    public function testFormOptionname()
    {
        $this->formBuilder->setOptionName('option');
        $this->assertEquals('option', $this->formBuilder->getOptionName());

        $this->formBuilder->deleteOptionName();
        $this->assertNull($this->formBuilder->getOptionName());
    }

    public function testFormSelect()
    {
        $months = array();
        foreach (range(1, 12) as $month) {
            $months[$month] = strftime('%B', mktime(0, 0, 0, $month, 1));
        }
        $form1 = $this->formBuilder->select('month', $months);
        $form2 = $this->formBuilder->select('month', $months, 1);
        $form3 = $this->formBuilder->select('month', $months, array(1,2));
        $form4 = $this->formBuilder->select('month', $months, null, array('id' => 'foo'));

        $this->formBuilder->setOptionName('option');
        $form5 = $this->formBuilder->select('month', $months);

        $food = array('Dairy products' => array('Cheese', 'Egg'), 'Vegetables' => array('Cabbage', 'Lettuce'));
        $form6 = $this->formBuilder->select('food', $food);
        $this->formBuilder->deleteOptionName();

        $this->assertContains('<select name="month"><option value="1">January</option><option value="2">February</option>', $form1);
        $this->assertContains('<select name="month"><option value="1" selected="selected">January</option>', $form2);
        $this->assertContains('<select name="month"><option value="1" selected="selected">January</option><option value="2" selected="selected">February</option><option value="3">March</option>', $form3);
        $this->assertContains('<select id="foo" name="month"><option value="1">January</option>', $form4);
        $this->assertContains('<select name="option[month]"><option value="1">January</option><option value="2">February</option>', $form5);
        $this->assertContains('<select name="option[food]"><optgroup label="Dairy products"><option value="0">Cheese</option><option value="1">Egg</option></optgroup><optgroup label="Vegetables"><option value="0">Cabbage</option><option value="1">Lettuce</option></optgroup></select>', $form6);
    }

    public function testFormSubmit()
    {
        $form1 = $this->formBuilder->submit('submit', 'foo');
        $form2 = $this->formBuilder->submit('submit', 'foo', array('class' => 'span2'));

        $this->assertEquals('<p class="submit"><input type="submit" name="submit" value="foo" /></p>', $form1);
        $this->assertEquals('<p class="submit"><input type="submit" name="submit" value="foo" class="span2" /></p>', $form2);
    }

    public function testOutput()
    {
        $form1 = $this->formBuilder->output('foo', 'baz');
        $this->formBuilder->openTable();
        $form2 = $this->formBuilder->output('foo', 'baz');
        $this->formBuilder->closeTable();

        $this->assertEquals('foobaz', $form1);
        $this->assertEquals('<tr><th scope="row">foo</th><td>baz</td></tr>', $form2);
    }
}
