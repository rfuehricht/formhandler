<?php

namespace Rfuehricht\Formhandler\Session;

use Rfuehricht\Formhandler\Utility\FormUtility;
use Rfuehricht\Formhandler\Utility\Globals;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * An abstract session class for Formhandler
 *
 */
abstract class AbstractSession implements SingletonInterface
{

    /**
     * An indicator if a session was already started
     *
     * @access protected
     * @var int
     */
    protected int $started = 0;

    public function __construct(
        protected readonly FormUtility $formUtility,
        protected readonly Globals     $globals
    )
    {

    }

    /**
     * Starts a new session
     *
     * @return void
     */
    abstract public function start(): void;

    /**
     * Sets a key
     *
     * @param string $key The key
     * @param mixed $value The value to set
     * @return void
     */
    abstract public function set(string $key, mixed $value): void;

    /**
     * Sets multiple keys at once
     *
     * @param array $values key value pairs
     * @return void
     */
    abstract public function setMultiple(array $values): void;

    /**
     * Get the value of the given key
     *
     * @param string $key The key
     * @return mixed The value
     */
    abstract public function get(string $key): mixed;

    /**
     * Checks if a session exists
     *
     * @return bool
     */
    abstract public function exists(): bool;

    /**
     * Resets all session values
     *
     * @return void
     */
    abstract public function reset(): void;

}
