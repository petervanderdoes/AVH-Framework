<?php
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

    public function testHtmlEmail()
    {
        // Normally PHP seeds mt_rand, used for the obfuscate method, with some microtime based data, but we set it manually for testing so the result is the same everytime.
        mt_srand(0);
        $html1 = $this->htmlBuilder->mailto('foo@bar.com');
        mt_srand(0);
        $html2 = $this->htmlBuilder->mailto('foo@bar.com', 'Foo');
        mt_srand(0);
        $html3 = $this->htmlBuilder->mailto('foo@bar.com', 'Foo', array('class' => 'class1'));

        $this->assertEquals('<a href="&#x6d;&#x61;i&#108;&#x74;o&#x3a;foo@bar.com">foo@bar.com</a>', $html1);
        $this->assertEquals('<a href="&#x6d;&#x61;i&#108;&#x74;o&#x3a;foo@bar.com">Foo</a>', $html2);
        $this->assertEquals('<a href="&#x6d;&#x61;i&#108;&#x74;o&#x3a;foo@bar.com" class="class1">Foo</a>', $html3);
    }

    public function testHtmlAnchor()
    {
        $html1 = $this->htmlBuilder->anchor('');
        $html2 = $this->htmlBuilder->anchor('/foo/');
        $html3 = $this->htmlBuilder->anchor('http://foo.com');
        $html4 = $this->htmlBuilder->anchor('/foo', 'Title');
        $html5 = $this->htmlBuilder->anchor('/foo', 'Title', array('class' => 'class1'));

        $this->assertEquals('<a href="http://example.org/">http://example.org/</a>', $html1);
        $this->assertEquals('<a href="http://example.org/wp-content/plugins/foo/">http://example.org/wp-content/plugins/foo/</a>', $html2);
        $this->assertEquals('<a href="http://foo.com">http://foo.com</a>', $html3);
        $this->assertEquals('<a href="http://example.org/wp-content/plugins/foo">Title</a>', $html4);
        $this->assertEquals('<a href="http://example.org/wp-content/plugins/foo" class="class1">Title</a>', $html5);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownWhenFileIsEmpty()
    {
        $html1 = $this->htmlBuilder->image('');
    }

    public function testHtmlImage()
    {
        $html1 = $this->htmlBuilder->image('/foo/');
        $html2 = $this->htmlBuilder->image('http://foo.com');
        $html3 = $this->htmlBuilder->image('/foo', 'Title');
        $html4 = $this->htmlBuilder->image('/foo', 'Title', array('class' => 'class1'));

        $this->assertEquals('<img src="http://example.org/wp-content/plugins/foo/">', $html1);
        $this->assertEquals('<img src="http://foo.com">', $html2);
        $this->assertEquals('<img src="http://example.org/wp-content/plugins/foo" alt="Title">', $html3);
        $this->assertEquals('<img src="http://example.org/wp-content/plugins/foo" alt="Title" class="class1">', $html4);

    }
}
