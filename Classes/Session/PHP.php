<?php

namespace Rfuehricht\Formhandler\Session;

/**
 * A session class for Formhandler using PHP sessions
 */
class PHP extends AbstractSession
{

    public function set(string $key, mixed $value): void
    {
        $this->start();
        $data = $_SESSION['formhandler'];
        if (!isset($data[$this->globals->getRandomId()])) {
            $data[$this->globals->getRandomId()] = [];
        }
        $data[$this->globals->getRandomId()][$key] = $value;
        $_SESSION['formhandler'] = $data;
    }

    public function start(): void
    {
        if (!isset($_SESSION['formhandler'])) {
            $_SESSION['formhandler'] = [];
        }
        if (!$this->started) {
            $current_session_id = session_id();
            if (empty($current_session_id)) {
                session_start();
            }
            $this->started = time();

        }
    }

    public function setMultiple(array $values): void
    {
        if (!empty($values)) {
            $this->start();
            $data = $_SESSION['formhandler'];
            if (!isset($data[$this->globals->getRandomId()])) {
                $data[$this->globals->getRandomId()] = [];
            }
            foreach ($values as $key => $value) {
                $data[$this->globals->getRandomId()][$key] = $value;
            }
            $_SESSION['formhandler'] = $data;
        }
    }

    public function get(string $key): mixed
    {
        $this->start();
        $data = $_SESSION['formhandler'];
        if (!isset($data[$this->globals->getRandomId()])) {
            $data[$this->globals->getRandomId()] = [];
        }
        return $data[$this->globals->getRandomId()][$key] ?? null;
    }

    public function exists(): bool
    {
        $this->start();
        $data = $_SESSION['formhandler'];
        return is_array($data[$this->globals->getRandomId()]);
    }

    public function reset(): void
    {
        $this->start();
        unset($_SESSION['formhandler'][$this->globals->getRandomId()]);
    }

}
