<?php

    namespace ObjectivePHP\Invokable;
    
    use ObjectivePHP\ServicesFactory\Exception\Exception as ServicesFactoryException;
    use ObjectivePHP\ServicesFactory\ServiceReference;
    use ObjectivePHP\ServicesFactory\ServicesFactory;

    /**
     * Class Invokable
     *
     * Embeds any Objective PHP operation recognized as callable or equivalent.
     * This can be any actual callable (Closure instance, anonymous function,
     * a class name or an object exposing __invoke(), or a ServiceReference.
     *
     * The check top distinguish between those types is supposed to be done
     * at runtime, to preserve performances in case the Invokable is never
     * called, and to allow referenced callables to be declared afterwards.
     *
     *
     * @package ObjectivePHP\Invokable
     */
    class Invokable implements InvokableInterface
    {

        /**
         * @var mixed Operation or operation reference
         */
        protected $operation;

        /**
         * @var ServicesFactory
         */
        protected $servicesFactory;

        /**
         * Invokable constructor.
         *
         * @param $operation
         */
        public function __construct($operation)
        {
            $this->operation = $operation;
        }

        /**
         * Returns an Invokable operation
         *
         * @param $invokable
         *
         * @return static
         */
        public static function cast($invokable)
        {
            return ($invokable instanceof InvokableInterface) ? $invokable : new static($invokable);
        }

        /**
         * Proxy to run() method
         *
         * @param array $args
         *
         * @return mixed
         * @internal param ApplicationInterface $app
         *
         */
        public function __invoke(...$args)
        {
            return $this->run(...$args);
        }

        /**
         * @return string
         */
        public function getDescription() : string
        {

            $operation = $this->operation;

            switch (true)
            {
                case $operation instanceof ServiceReference:
                    $description = 'Referenced service "' . $operation->getId() . '"';
                    break;

                case $operation instanceof \Closure:
                    $reflected   = new \ReflectionFunction($operation);
                    $description = sprintf('Closure defined in file "%s" on line %d', $reflected->getFileName(), $reflected->getStartLine());
                    break;

                case is_object($operation):
                    $description = 'Instance of ' . get_class($operation);
                    break;

                case is_string($operation) && class_exists($operation):
                    $description = 'Invokable class ' . $operation;
                    break;

                default:
                    $description = 'Unknown operation type';
                    break;
            }

            return $description;
        }

        /**
         * Run the operation
         *
         * @param array $args
         *
         * @return mixed
         * @throws Exception
         * @internal param ApplicationInterface $app
         *
         */
        public function run(...$args)
        {
            $callable = $this->getCallable();

            return $callable(...$args);
        }

        /**
         * @return callable
         *
         * @throws Exception
         * @throws \ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException
         */
        public function getCallable()
        {
            $operation = $this->operation;
            try
            {
                if (!is_callable($operation))
                {
                    if ($operation instanceof ServiceReference)
                    {
                        $serviceId = $operation->getId();

                        if (is_null($this->servicesFactory))
                        {
                            throw new Exception(sprintf('No ServicesFactory is available to build referenced service "%s"', $serviceId));
                        }

                        if (!$this->servicesFactory->has($operation))
                        {
                            throw new Exception(sprintf('Referenced service "%s" is not registered', $serviceId), Exception::REFERENCED_SERVICE_IS_NOT_REGISTERED);
                        }

                        try
                        {
                            $operation = $this->getServicesFactory()->get($operation);
                        } catch (ServicesFactoryException $e)
                        {
                            throw new Exception(sprintf('An error occurred when building referenced service "%s"', $serviceId), Exception::REFERENCED_SERVICE_BUILD_ERROR, $e);
                        }

                        if (!is_callable($operation))
                        {
                            throw new Exception(sprintf('Referenced service "%s" is not an instance of a callable class ("%s" should implement __invoke())', $serviceId, get_class($operation)), Exception::REFERENCED_SERVICE_IS_NOT_CALLABLE);
                        }
                    }
                    elseif (class_exists($operation))
                    {
                        $operation = new $operation;

                        if (!is_callable($operation))
                        {
                            throw new Exception(sprintf('Class "%s" is not callable (it should implement __invoke())', get_class($operation)), Exception::CLASS_IS_NOT_INVOKABLE);
                        }
                    }
                    else
                    {
                        throw new Exception(sprintf('Class "%s" does not exist', $operation), Exception::CLASS_DOES_NO_EXIST);
                    }
                }

            } catch (Exception $e)
            {
                throw new Exception(sprintf('Cannot run operation: %s', $this->getDescription()), Exception::FAILED_RUNNING_OPERATION, $e);
            }

            return $operation;
        }

        /**
         * @return ServicesFactory
         */
        public function getServicesFactory()
        {
            return $this->servicesFactory;
        }

        /**
         * @param ServicesFactory $servicesFactory
         *
         * @return $this
         */
        public function setServicesFactory(ServicesFactory $servicesFactory)
        {
            $this->servicesFactory = $servicesFactory;

            return $this;
        }

    }
