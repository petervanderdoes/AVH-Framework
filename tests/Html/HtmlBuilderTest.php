<?php
use Avh\Html\FormBuilder;
use Avh\Html\HtmlBuilder;
use Illuminate\Container\Container;

class HtmlBuilderTest extends WP_UnitTestCase
{

    /**
     *
     * @var HtmlBuilder
     */
    public $htmlBuilder;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $container = new Container();

        $this->htmlBuilder = $container->make('Avh\Html\HtmlBuilder');
        parent::setUp();
    }

    public function testAttributes()
    {
        $attribute1=$this->htmlBuilder->attributes(array('class'=>'class-1'));
        $attribute2=$this->htmlBuilder->attributes(array(5=>'selected'));
        $attribute3=$this->htmlBuilder->attributes(array());

        $this->assertEquals(' class="class-1"', $attribute1);
        $this->assertEquals(' selected="selected"', $attribute2);
        $this->assertEmpty($attribute3);
    }
}
