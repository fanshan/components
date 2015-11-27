<?php
    

    namespace Tests\ObjectivePHP\DataSet;

    use ObjectivePHP\DataSet\DataSet;
    use ObjectivePHP\DataSet\Exception;
    use ObjectivePHP\PHPUnit\TestCase;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Notification;

    class DataSetTest extends TestCase
    {
        public function testValidatorsAreInitializedAsAnEmptyCollection()
        {
            $dataSet = new DataSet();

            $this->assertInstanceOf(Collection::class, $dataSet->getValidators());
        }

        public function testDataHydrationAtInstantiation()
        {
            $dataSet = new DataSet(['test' => 'value']);

            $this->assertEquals($dataSet['test'], 'value');
        }

        public function testDataHydration()
        {
            $dataSet = new DataSet();

            // with an array
            $dataSet->hydrate(['test' => 'value']);
            $this->assertEquals($dataSet['test'], 'value');

            // with an ArrayObject
            $dataSet->hydrate(new \ArrayObject(['test' => 'value']));
            $this->assertEquals($dataSet['test'], 'value');

            // with an ArrayObject
            $dataSet->hydrate(new \ArrayIterator(['test' => 'value']));
            $this->assertEquals($dataSet['test'], 'value');


            // with an invalid data set
            $this->expectsException(function () use ($dataSet)
            {
                $dataSet->hydrate('this is not a valid data set');
            }, Exception::class, null, Exception::INVALID_DATA);

        }

        public function testDataExtraction()
        {
            $data = ['test' => 'value'];

            $dataSet = new DataSet($data);

            $this->assertEquals($data, $dataSet->toArray());
        }

        public function testIndividualItemValueSettingUsingArrayNotation()
        {
            $dataSet         = new DataSet();
            $dataSet['test'] = 'value';
            $this->assertEquals('value', $dataSet->get('test'));
        }

        public function testIndividualItemValueUsingSetter()
        {
            $dataSet = new DataSet();
            $dataSet->set('test', 'value');
            $this->assertEquals('value', $dataSet->get('test'));
        }

        public function testIndividualItemValueUsingSetterAndMapping()
        {
            $dataSet = new DataSet();
            $dataSet->map('test_value', 'test');
            $dataSet->set('test_value', 'value');
            $this->assertEquals('value', $dataSet->get('test'));
            $this->assertEquals(null, $dataSet->get('test_value'));

            $dataSet->unmap('test_value');
            $dataSet->set('test_value', 'value');
            $this->assertEquals('value', $dataSet->get('test_value'));
        }

        public function testDataHydrationWithMapping()
        {
            $dataSet = new DataSet();

            // with an ArrayObject
            $dataSet->map('test_value', 'test');
            $dataSet->hydrate(['test_value' => 'other value']);
            $this->assertEquals($dataSet['test'], 'other value');

        }

        public function testValidationMechanism()
        {

            $dataSet = new DataSet();

            $this->assertSame(true, $dataSet->isValid());

            $validator = $this->getMock(Validator::class);
            $validator->expects($this->once())->method('__invoke')->with($dataSet)->willReturn(false);

            $dataSet->setValidators($validator);

            $this->assertSame(false, $dataSet->isValid());

        }


        public function testActualValidation()
        {
            $dataSet = new DataSet();

            $this->assertSame(true, $dataSet->isValid());

            $dataSet->setValidators(new ActualValidator());

            $this->assertFalse($dataSet->isValid());
            // check notifications
            $this->assertEquals(1, $dataSet->getNotifications()->count());


            $dataSet->hydrate(['test' => -1]);
            $this->assertFalse($dataSet->isValid());


            // check notifications
            $this->assertEquals(1, $dataSet->getNotifications()->count());

            $dataSet->hydrate(['test' => 1]);
            $this->assertTrue($dataSet->isValid());

            // check notifications
            $this->assertEquals(0, $dataSet->getNotifications()->count());

        }


    }


    class Validator
    {

        function __invoke(DataSet $dataSet)
        {
            return false;
        }

    }


    class ActualValidator
    {

        function __invoke(DataSet $dataSet, Notification\Stack $notifications)
        {
            $isValid = true;
            if (!isset($dataSet['test']))
            {
                $notifications->addMessage('test.missing', new Notification\Alert('Mandatory item "test" is missing from the data set'));
                $isValid = false;
            }
            else
            {
                if ($dataSet['test'] < 0)
                {
                    $notifications->addMessage('test.negative', new Notification\Alert('"test" value should not be negative'));
                    $isValid = false;
                }
            }

            return $isValid;
        }

    }