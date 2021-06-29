<?php


namespace Application\Model\Traits;


use Laminas\Db\Adapter\Adapter;

trait DbAdapterAwareTrait
{
    /**
     * @var Adapter
     */
    protected $db = null;

    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     *
     * @return self Provides a fluent interface
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->db = $adapter;

        return $this;
    }
}
