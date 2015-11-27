<?php
    
    namespace Test\ObjectivePHP\Config;

    use ObjectivePHP\Config\Config;
    use ObjectivePHP\Config\Exception;
    use ObjectivePHP\Matcher\Matcher;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Merger\MergePolicy;
    use ObjectivePHP\Primitives\Merger\ValueMerger;

    class ConfigTest extends TestCase
    {

        public function testDeepAccess()
        {

            $config =  new Config([
                            'app.version'   => 1.0,
                            'app.env'       => 'test',
                            'db.config.host' => 'localhost',
                            'db.config.user' => 'plop',
                            'db.driver' => 'pgsql'
            ]);

            $appConfig = $config->app;
            $this->assertInstanceOf(Config::class, $appConfig);
            $this->assertEquals(['app.version' => 1.0, 'app.env' => 'test'], $appConfig->getDirectives());

            $appConfig = $config['app'];
            $this->assertInstanceOf(Config::class, $appConfig);
            $this->assertEquals(['app.version' => '1.0', 'app.env' => 'test'], $appConfig->getDirectives());

            $appConfig = $config->get('app');
            $this->assertInstanceOf(Config::class, $appConfig);
            $this->assertEquals(['app.version' => '1.0', 'app.env' => 'test'], $appConfig->getDirectives());


            $this->assertEquals('1.0', $appConfig->version);

        }

        public function testFQNAdditions()
        {
            $config = new Config([
                'app.version'    => 1.0,
                'app.env'        => 'test',
            ]);

            $config->app->set('debug', true);

            $this->assertTrue($config->app->debug);
            //$this->assertTrue($config->get('app.debug'));
        }

        public function testMergingValuesWithFQN()
        {

            $config = new Config([
                'debug.environments' => 'test',
            ]);


            $otherConfig = (new Config([
                'debug.environments' => 'dev',
            ]))->addMerger('debug.environments', new ValueMerger(MergePolicy::NATIVE));

            $config->merge($otherConfig);

            $this->assertEquals(['test', 'dev'], $config->debug->environments);


        }

        public function testMergingValuesWithShortNames()
        {

            $config = new Config([
                'debug.environments' => 'test',
            ]);


            $otherConfig = (new Config())->debug->fromArray([
                'environments' => 'dev',
            ])
                ->addMerger('environments', new ValueMerger(MergePolicy::NATIVE));

            $config->merge($otherConfig);
            //$this->assertEquals(['test', 'dev'], $config->debug->environments->toArray());


        }

        public function testFactory()
        {
            $config = Config::factory([
                'section' => 'app',
                'mergers' => ['tokens' => MergePolicy::NATIVE],
                'validators' => [
                    function($value) { return true; }
                ],
                'directives' =>
                [
                    'app.version' => '1.1',
                    'environment' => 'dev',
                    'tokens' => 'first',
                    'other.subsection' => 'test'
                ]
            ]);

            $this->assertEquals('1.1', $config->app->version);

            $otherConfig = (new Config())->app->set('tokens', 'second');
            $config->merge($otherConfig);


            $this->assertEquals(['first', 'second'], $config->app->tokens);
            $this->assertEquals('test', $config->app->other->subsection);
        }


        public function testFactoryWithoutSection()
        {
            $config = Config::factory([
                'app.version'   => '1.1',
                'environment'   => 'dev',
                'tokens'        => 'first',
                'other.directive' => 'test'
            ]);

            $this->assertEquals('test', $config->other->directive);
        }

        public function testConfigForbidsToSetDirectivesMatchingSectionName()
        {
            $config = new Config(['app.version' => '1.0']);

            $this->expectsException(function() use ($config)
            {
                $config->set('app', 'this is forbidden because app.version already exists!');
            }, Exception::class, null, Exception::FORBIDDEN_DIRECTIVE_NAME);
        }

        public function testConfigForbidsToSetSectionsMatchingDirectiveName()
        {
            $config = new Config(['app.name' => 'my app']);

            $this->expectsException(function() use ($config)
            {
                $config->set('app.name.version', 'this is forbidden because app already exists!');
            }, Exception::class, null, Exception::FORBIDDEN_SECTION_NAME);
        }

        public function testFluentAccess()
        {
            $config = new Config([
                'a.b' => 'x',
                'c.d' => ['y'],
            ]);

            $this->assertInstanceOf(Config::class, $config->c);
            $this->assertEquals('c', $config->c->getSection());

            $this->assertEquals(['y'], $config->c->d);
        }

        public function testMatcherAccessors()
        {
            $matcher = new Matcher();

            $config = (new Config)->setMatcher($matcher);

            $this->assertAttributeSame($matcher, 'matcher', $config);
            $this->assertSame($matcher, $config->getMatcher());
        }

        public function testDefaultValueIsReturnedWhenRequestedDirectiveDoesNotExist()
        {
            $config = new Config(['x.y' => 'a']);


            $default = $config->get('x.z', 'b');

            $this->assertEquals('b', $default);
        }


        public function testValueSettingUsingObjectSyntax()
        {
            $config = new Config;

            $config->z = 'test_z';
            $config->a->b->c = 'test_a_b_c';
            $config->x->y = 'test_x_y';
            $config->a->d->c = 'test_a_d_c';
            $config->a->d->e = 'test_a_d_e';

            $this->assertEquals('test_z', $config->z);
            $this->assertEquals('test_x_y', $config->x->y);
            $this->assertEquals('test_a_b_c', $config->a->b->c);
            $this->assertEquals('test_a_d_c', $config->get('a.d.c'));

            $this->assertInstanceOf(Config::class, $config->get('a'));
            $this->assertInstanceOf(Config::class, $config->get('a')->d);
            $this->assertInstanceOf(Config::class, $config->get('a')['d']);
            $this->assertEquals('test_a_d_c', $config->get('a')->d['c']);
            $this->assertEquals('test_a_d_c', $config->get('a')['d']->c);


            $config['a']['d.c'] = 'yeah';

            $this->assertEquals('yeah', $config->get('a')['d']->c);

        }

        public function testDeepAccessDoesNotSetParentSection()
        {
            $config = new Config([
                'a.b.c' => ['d', 'e']
            ]);

            $c = $config->a->b->c;

            $this->assertNull($config->getSection());
            $this->assertEquals('b.c',$config->b->c->getSection());

        }

        public function testHasDirective()
        {
            $config = new Config([
                'a.b.c' => ['d', 'e']
            ]);

            $this->assertFalse($config->hasDirective('a'));
            $this->assertTrue($config->hasDirective('a.b.c'));
        }


        public function testSettingSubKeysFromArray()
        {
            $config = new Config();

            $config->a->fromArray(['b.c' => 'x', 'b.d' => 'y']);

            $this->assertEquals('x', $config->a->b->c);

        }

        public function testSettingSubKeysFromConfig()
        {
            $config = new Config([
                'a' => new Config(['b' => 'c'])
            ]);

            $this->assertEquals(['a.b' => 'c'], $config->getDirectives());
        }

        public function testToArray()
        {
            $config = new Config([
               'a.b.c' => 'x',
               'a.b.d' => 'y',
               'a.c.e' => 'z',
               'b' => 'other'
            ]);


            $this->assertEquals([
                'a' => [
                    'b' => [
                        'c' => 'x',
                        'd' => 'y'
                    ],
                    'c' => [
                        'e' => 'z'
                    ]
                ],
                'b' => 'other'
            ], $config->toArray());

            $this->assertEquals([
                'b' => [
                    'c' => 'x',
                    'd' => 'y'
                ],
                'c' => [
                    'e' => 'z'
                ]
            ], $config->a->toArray());
        }
    }
