<?php
    /**
     * This file is part of the Objective PHP project
     *
     * More info about Objective PHP on www.objective-php.org
     *
     * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
     */
    
    namespace ObjectivePHP\Config;
    
    
    abstract class AbstractDirective implements DirectiveInterface
    {

        /**
         * @var string Directive description
         */
        protected $description;

        /**
         * @var mixed Directive value
         */
        protected $value;

        /**
         * @var bool
         */
        protected $isOverrideAllowed = true;


        /**
         * @return $this
         */
        public function denyOverride()
        {
            $this->isOverrideAllowed = false;

            return $this;
        }

        /**
         * @return $this
         */
        public function allowOverride()
        {
            $this->isOverrideAllowed = true;

            return $this;
        }

        /**
         * @return string
         */
        public function getDescription() : string
        {
            return (string) $this->description;
        }

        /**
         * @return mixed
         */
        public function getValue()
        {
            return $this->value;
        }

    }
