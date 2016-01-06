<?php
    /**
     * This file is part of the Objective PHP project
     *
     * More info about Objective PHP on www.objective-php.org
     *
     * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
     */
    
    namespace ObjectivePHP\Config;
    
    
    abstract class MultipleDirective extends AbstractDirective
    {
        /**
         * Directive configuration prefix (will be used as key in the Config object)
         */
        const PREFIX = 'THIS HAS TO BE SET IN INHERITED CLASSES';

        /**
         * @var bool
         */
        protected $isOverrideAllowed = true;

        /**
         * @var string Identifier prefix
         */
        protected $prefix;

        /**
         * Directive configuration identifier (will be used as key in the Config object)
         */
        protected $identifier;

        /**
         * ScalarDirective constructor.
         *
         * @param $identifier
         * @param $value
         */
        public function __construct($identifier, $value)
        {
            $this->identifier = $identifier;
            $this->value      = $value;
        }

        /**
         * @param ConfigInterface $config
         *
         * @return DirectiveInterface
         * @throws Exception
         */
        public function mergeInto(ConfigInterface $config) : DirectiveInterface
        {
            $identifier = sprintf('%s.%s', static::PREFIX, $this->identifier);

            // only set directive if it is not present or if it can be overridden
            if ($config->lacks($identifier) || $this->isOverrideAllowed)
            {
                $config->set($identifier, $this->getValue());
            }

            return $this;
        }

    }
