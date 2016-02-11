<?php

    namespace ObjectivePHP\Invokable;

    use ObjectivePHP\ServicesFactory\ServicesFactory;


    /**
     * Interface InvokableInterface
     *
     * @package ObjectivePHP\Invokable
     */
    interface InvokableInterface
    {
        /**
         * Run the operation
         *
         * @param mixed                ...$args
         *
         * @return mixed
         */
        public function __invoke(...$args);

        /**
         * Return short description
         *
         * @return string
         */
        public function getDescription() : string;

        /**
         * @param ServicesFactory $factory
         *
         * @return mixed
         */
        public function setServicesFactory(ServicesFactory $factory);

    }
