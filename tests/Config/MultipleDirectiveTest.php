<?php
    
    namespace Test\ObjectivePHP\Config;

    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Config\MultipleDirective;
    use ObjectivePHP\PHPUnit\TestCase;

    class StackDirectiveTest extends TestCase
    {

        public function testMultipleDirectiveImport()
        {
            $config = new Config();

            $directive = new TestMultipleDirective('first', 'test value');

            $config->import($directive);

            $this->assertCount(1, $config);
            $this->assertTrue($config->has('test.directive.first'));
            $this->assertEquals('test value', $config->get(TestMultipleDirective::PREFIX . '.first'));

            $config->import(new TestMultipleDirective('second', 'other value'));

            $this->assertCount(2, $config);
            $this->assertFalse($config->has('test.directive'));
            $this->assertEquals('other value', $config->get('test.directive.second'));

        }

        public function testOverridingBehaviour()
        {
            $config = new Config();

            $config->import(new TestMultipleDirective('first', 'other value'));

            $this->assertCount(1, $config);
            $this->assertTrue($config->has('test.directive.first'));
            $this->assertEquals('other value', $config->get('test.directive.first'));


            $config->import(new TestMultipleDirective('second', 'stacked value'));

            $this->assertCount(2, $config);
            $this->assertTrue($config->has('test.directive.second'));
            $this->assertEquals('stacked value', $config->get('test.directive.second'));


            // override previously imported value (default behaviour)
            $config->import(new TestMultipleDirective('second', 'overriding value'));

            $this->assertCount(2, $config);
            $this->assertTrue($config->has('test.directive.second'));

            // next import is ignored because overriding ability has been denied to hte directive
            $config->import((new TestMultipleDirective('second', 'over overriding value'))->denyOverride());

            $this->assertEquals('overriding value', $config->get('test.directive.second'));

            // check that a Multiple Directive full content can be retrieved as subset
            $this->assertEquals(['first' => 'other value', 'second' => 'overriding value'], $config->subset(TestMultipleDirective::PREFIX)
                                                                                                   ->toArray());
        }

    }


    class TestMultipleDirective extends MultipleDirective
    {
        const PREFIX = 'test.directive';
    }

