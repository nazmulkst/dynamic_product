<?php
namespace Straitv\DynamicProduct\Api;

interface ProductCreateManagementInterface
{
    /**
     * Create the specified products in item array.
     *
     * @api
     * @param mixed $data
     * @return boolean
     */
    public function createProduct($products);
}
