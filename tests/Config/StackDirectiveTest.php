<?php
    
    namespace Test\ObjectivePHP\Config;

    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Config\StackDirective;
    use ObjectivePHP\PHPUnit\TestCase;

    class StackDirectiveTest extends TestCase
    {

        public function testStackDirectiveImport()
        {
            $config = new Config();

            $directive = new TestStackDirective('test value');

            $config->import($directive);

            $this->assertCount(1, $config);
            $this->assertTrue($config->has(TestStackDirective::DIRECTIVE));
            $this->assertEquals(['test value'], $config->get(TestStackDirective::DIRECTIVE));

            $config->import(new TestOtherStackDirective('other value'));

            $this->assertCount(2, $config);
            $this->assertTrue($config->has(TestOtherStackDirective::DIRECTIVE));
            $this->assertEquals(['other value'], $config->get(TestOtherStackDirective::DIRECTIVE));

        }

        public function testOverridingBehaviour()
        {
            $config = new Config();

            $config->import(new TestStackDirective('other value'));

            $this->assertCount(1, $config);
            $this->assertTrue($config->has(TestStackDirective::DIRECTIVE));
            $this->assertEquals(['other value'], $config->get(TestStackDirective::DIRECTIVE));

            // this import will stack the second value, because stacking is the default behaviour for StackDirective
            $config->import(new TestStackDirective('stacked value'));

            $this->assertCount(1, $config);
            $this->assertTrue($config->has(TestStackDirective::DIRECTIVE));
            $this->assertEquals(['other value', 'stacked value'], $config->get(TestStackDirective::DIRECTIVE));


            // next import is ignored because overriding ability has been denied to hte directive
            $config->import((new TestStackDirective('overriding value'))->allowOverride());

            $this->assertCount(1, $config);
            $this->assertTrue($config->has(TestStackDirective::DIRECTIVE));
            $this->assertEquals(['overriding value'], $config->get(TestStackDirective::DIRECTIVE));

        }

    }


    class TestStackDirective extends StackDirective
    {
        const DIRECTIVE = 'test.directive';
    }

    class TestOtherStackDirective extends StackDirective
    {
        const DIRECTIVE = 'other.directive';
    }

