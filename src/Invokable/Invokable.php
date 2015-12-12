<?php

    namespace ObjectivePHP\Invokable;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\ServicesFactory\ServiceReference;

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
         * Invokable constructor.
         *
         * @param $operation
         */
        public function __construct($operation)
        {
            $this->operation = $operation;
        }

        /**
         * Run the operation
         *
         * @param ApplicationInterface $app
         *
         * @return mixed
         */
        public function run(ApplicationInterface $app)
        {

            $callable = $this->getCallable($app);

            return $callable($app);
        }

        /**
         * @param ApplicationInterface $app
         * @return callable
         *
         * @throws Exception
         * @throws \ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException
         */
        public function getCallable(ApplicationInterface $app)
        {
            $operation = $this->operation;

            if (!is_callable($operation))
            {
                if ($operation instanceof ServiceReference)
                {
                    $operation = $app->getServicesFactory()->get($operation);
                }
                elseif (class_exists($operation))
                {
                    $operation = new $operation;
                }
            }


            if (!is_callable($operation))
            {
                throw new Exception(sprintf('Cannot run operation: %s', $this->getDescription()));
            }

            return $operation;
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
                    $description = 'Service "' . $operation->getId() . '"';
                    break;

                case $operation instanceof \Closure:
                    $reflected = new \ReflectionFunction($operation);

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
         * @param ApplicationInterface $app
         *
         * @return mixed
         */
        public function __invoke(ApplicationInterface $app)
        {
            return $this->run($app);
        }

    }