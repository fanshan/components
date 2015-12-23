<?php

    namespace ObjectivePHP\DataSet;
    
    
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Notification;

    /**
     * Class DataSet
     *
     * @package ObjectivePHP\DataSet
     */
    class DataSet implements \ArrayAccess
    {

        /**
         * @var Collection
         */
        protected $validators;

        /**
         * @var array
         */
        protected $data = [];

        /**
         * @var array
         */
        protected $map;

        /**
         * @var Notification\Stack
         */
        protected $notifications;

        /**
         * DataSet constructor.
         *
         * @param array $data
         */
        public function __construct($data = [])
        {
            $this->initValidators();

            $this->init();

            $this->hydrate($data);
        }

        /**
         * Delegated constructor
         */
        public function init()
        {

        }

        /**
         *
         */
        protected function initValidators()
        {
            $this->validators = new Collection();
        }

        /**
         * Feed the data set with external data
         *
         * @param $data
         */
        public function hydrate($data) : DataSet
        {

            if ($data instanceof \ArrayObject)
            {
                $data = $data->getArrayCopy();
            }
            elseif ($data instanceof \Iterator)
            {
                $data = iterator_to_array($data);
            }

            if (!is_array($data))
            {
                throw new Exception('Trying to hydrate DataSet object with invalid data', Exception::INVALID_DATA);
            }

            foreach($data as $item => $value)
            {
                $this->set($item, $value);
            }

            return $this;
        }

        /**
         * Extract internal data array
         *
         * @return array
         */
        public function toArray() : array
        {
            return $this->data;
        }

        /**
         * @param $item
         *
         * @return mixed
         */
        public function get($item)
        {
            return $this->data[$item] ?? null;
        }

        /**
         * @param $item
         *
         * @return $this
         */
        public function set($item, $value) : DataSet
        {
            $item = $this->map[$item] ?? $item;

            $this->data[$item] = $value;

            return $this;
        }

        /**
         * Map some item to another
         *
         * For instance, map('some', 'item') will make set('some', 'value')
         * assign 'value' to 'item', not to 'some'
         *
         * @param $item
         * @param $target
         *
         * @return $this
         */
        public function map($item, $target)
        {

            $this->map[$item] = $target;

            return $this;
        }

        /**
         * @param $item
         * @param $target
         *
         * @return $this
         */
        public function unmap($item)
        {

            unset($this->map[$item]);

            return $this;
        }

        /**
         * @return bool
         * @throws \ObjectivePHP\Primitives\Exception
         */
        public function isValid() : bool
        {

            $isValid = true;
            $notifications = new Notification\Stack();

            $this->getValidators()->each(function (callable $validator) use (&$isValid, $notifications)
            {
                if (!$result = $validator($this, $notifications)) $isValid = false;
            })
            ;

            $this->setNotifications($notifications);

            return $isValid;
        }

        /**
         * @return Collection
         */
        public function getValidators() : Collection
        {
            return $this->validators;
        }

        /**
         * @param mixed $validators
         *
         * @return $this
         */
        public function setValidators($validators)
        {
            $this->validators = Collection::cast($validators);

            return $this;
        }

        /**
         * @param $validator
         */
        public function addValidator($validator)
        {
            $this->validators->append($validator);

            return $this;
        }

        /**
         * @param mixed $offset
         *
         * @return bool
         */
        public function offsetExists($offset)
        {
            return array_key_exists($offset, $this->data);
        }

        /**
         * @param mixed $offset
         *
         * @return mixed
         */
        public function offsetGet($offset)
        {
            return $this->data[$offset];
        }

        /**
         * @param mixed $offset
         * @param mixed $value
         */
        public function offsetSet($offset, $value)
        {
            $this->data[$offset] = $value;
        }

        /**
         * @param mixed $offset
         */
        public function offsetUnset($offset)
        {
            unset($this->data[$offset]);
        }

        /**
         * @return Notification\Stack
         */
        public function getNotifications()
        {
            return $this->notifications;
        }

        /**
         * @param Notification\Stack $notifications
         *
         * @return $this
         */
        protected function setNotifications(Notification\Stack $notifications)
        {
            $this->notifications = $notifications;

            return $this;
        }

    }