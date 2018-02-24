<?php
namespace App\Helper\Messages;

/**
 * Collection of messages
 */
class MessageCollection
{
    protected $messages;

    /**
     * Adds a message to a given key.
     *
     * @param mixed $key
     * @param mixed $message
     * @return void
     */
    public function add($key, $message)
    {
        $this->messages[$key][] = $message;
    }

    /**
     * Checks if there are any messsages with a specific key.
     *
     * @param mixed $key
     * @return void
     */
    public function has($key): bool
    {
        return !empty($this->messages[$key]);
    }

    /**
     * Checks if collection contains a key value pair.
     *
     * @param mixed $key
     * @param mixed $value
     * @return bool
     */
    public function hasValue($key, $value): bool
    {
        if (!$this->has($key)) {
            return false;
        }

        return array_search($value, $this->get($key)) !== false;
    }

    /**
     * Returns all messages having a specific key.
     *
     * @param mixed $key
     * @return mixed
     */
    public function get($key): array
    {
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }
        return [];
    }

    public function remove($key): bool
    {
        if ($this->has($key)) {
            unset($this->messages[$key]);
            return true;
        }

        return false;
    }

    public function removeValue($key, $value): bool
    {
        if ($this->has($key)) {
            $idx = array_search($value, $this->messages[$key]);

            if ($idx !== false) {
                array_splice($this->messages[$key], $idx, 1);
                if (count($this->messages[$key]) == 0) {
                    unset($this->messages[$key]);
                }

                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Checks if the collections has any messages.
     *
     * @return bool
     */
    public function any(): bool
    {
        return !empty($this->messages);
    }

    /**
     * Gets all messages regardless of the key.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->messages;
    }

    /**
     * Copies all messages from another collection to this instance.
     *
     * @param MessageCollection $other
     * @return void
     */
    public function merge(MessageCollection $other)
    {
        if ($other->any()) {
            foreach ($other->all() as $key => $messages) {
                foreach ($messages as $message) {
                    if (!$this->hasValue($key, $message)) {
                        $this->add($key, $message);
                    }
                }
            }
        }
    }
}
