<?php

    namespace ObjectivePHP\Invokable;

    use ObjectivePHP\Application\ApplicationInterface;

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
         * @param ApplicationInterface $app
         * @param mixed ...$args
         *
         * @return mixed
         */
        public function __invoke(ApplicationInterface $app);

        /**
         * @return string
         */
        public function getDescription() : string;

    }