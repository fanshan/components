<?php
    
    namespace Test\ObjectivePHP\Config;

    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Config\ScalarDirective;
    use ObjectivePHP\PHPUnit\TestCase;

    class ScalarDirectiveTest extends TestCase
    {

        public function testScalarDirectiveImport()
        {
            $config = new Config();

            $directive = new TestScalarDirective('test value');

            $config->import($directive);

            $this->assertCount(1, $config);
            $this->assertTrue($config->has(TestScalarDirective::DIRECTIVE));
            $this->assertEquals('test value', $config->get(TestScalarDirective::DIRECTIVE));

            $config->import(new TestOtherScalarDirective('other value'));

            $this->assertCount(2, $config);
            $this->assertTrue($config->has(TestOtherScalarDirective::DIRECTIVE));
            $this->assertEquals('other value', $config->get(TestOtherScalarDirective::DIRECTIVE));

        }

        public function testOverridingBehaviour()
        {
            $config = new Config();

            $config->import(new TestScalarDirective('other value'));

            $this->assertCount(1, $config);
            $this->assertTrue($config->has(TestScalarDirective::DIRECTIVE));
            $this->assertEquals('other value', $config->get(TestScalarDirective::DIRECTIVE));

            // this import will override previous one, because overriding is allowed by default
            // on scalar directives
            $config->import(new TestScalarDirective('overriding value'));

            // next import is ignored because overriding ability has been denied to hte directive
            $config->import((new TestScalarDirective('ignored value'))->denyOverride());

            $this->assertCount(1, $config);
            $this->assertTrue($config->has(TestScalarDirective::DIRECTIVE));
            $this->assertEquals('overriding value', $config->get(TestScalarDirective::DIRECTIVE));

        }

    }


    class TestScalarDirective extends ScalarDirective
    {
        const DIRECTIVE = 'test.directive';
    }

    class TestOtherScalarDirective extends ScalarDirective
    {
        const DIRECTIVE = 'other.directive';
    }

